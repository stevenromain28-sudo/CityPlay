<?php

namespace App\Http\Controllers;

use App\Models\Ville;
use App\Models\Lieu;
use App\Models\Enigme;
use App\Models\SessionJeu;
use App\Models\JoueurSession;
use App\Models\TentativeEnigme;
use App\Models\ProgressionEnigme;
use App\Models\IndiceDebloque;
use App\Services\CityService;
use App\Services\SessionJeuService;
use Inertia\Inertia;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    protected $cityService;
    protected $sessionService;

    public function __construct(CityService $cityService, SessionJeuService $sessionService)
    {
        $this->cityService = $cityService;
        $this->sessionService = $sessionService;
    }

    public function dashboard(Request $request)
    {
        $user = auth()->user();
        $latitude = $request->query('lat');
        $longitude = $request->query('lng');

        // DEBUG
        \Log::info("Dashboard Player - Lat: $latitude, Lng: $longitude");

        $villeDetectee = null;
        $lieux = [];
        if ($latitude && $longitude) {
            $villeDetectee = $this->cityService->trouverVilleProche((float)$latitude, (float)$longitude);
            if ($villeDetectee) {
                $lieux = $villeDetectee->lieux()->withCount('enigmes')->get();
            }
        }

        // Récupérer les sessions de jeu du joueur
        $sessions = JoueurSession::where('user_id', $user->id)
            ->with(['sessionJeu.ville'])
            ->latest()
            ->take(5)
            ->get();

        // Calculer les stats du joueur
        $stats = [
            'total_score' => JoueurSession::where('user_id', $user->id)->sum('score'),
            'sessions_count' => JoueurSession::where('user_id', $user->id)->count(),
            'enigmes_resolues' => TentativeEnigme::where('user_id', $user->id)
                ->where('succes', true)
                ->count(),
            'villes_visitees' => JoueurSession::where('user_id', $user->id)
                ->join('sessions_jeu', 'joueur_sessions.session_jeu_id', '=', 'sessions_jeu.id')
                ->distinct('sessions_jeu.ville_id')
                ->count(),
        ];

        return Inertia::render('Player/Dashboard', [
            'stats' => $stats,
            'recent_sessions' => $sessions,
            'villes_disponibles' => Ville::where('actif', true)->get(),
            'ville_detectee' => $villeDetectee,
            'lieux' => $lieux,
            'localisation_requise' => !$latitude || !$longitude,
        ]);
    }

    /**
     * Dashboard spécifique à un lieu.
     */
    public function lieuDashboard(Ville $ville, Lieu $lieu)
    {
        return Inertia::render('Player/LieuDashboard', [
            'ville' => $ville,
            'lieu' => $lieu->loadCount('enigmes'),
            'enigmes' => $lieu->enigmes()->orderBy('ordre')->get(),
        ]);
    }

    public function jeu(Request $request, SessionJeu $session)
    {
        $user = auth()->user();
        $lat = (float) $request->query('lat');
        $lng = (float) $request->query('lng');

        // Synchroniser le temps avant de charger la page
        $this->sessionService->calculerTempsRestant($session);
        
        $enigme = null;

        // 1. Vérifier si une énigme spécifique est déjà définie dans la session
        if ($session->current_enigme_id) {
            $enigme = Enigme::with('indices')->find($session->current_enigme_id);
            // Vérifier si elle est déjà résolue par ce joueur
            $dejaResolue = TentativeEnigme::where('user_id', $user->id)
                ->where('enigme_id', $session->current_enigme_id)
                ->where('succes', true)
                ->exists();
            
            if ($dejaResolue) {
                $enigme = null; // On passera à la suite
            }
        }

        // 2. Vérifier si le joueur a des bonus en cours
        if (!$enigme) {
            $bonusEnCours = ProgressionEnigme::where('session_jeu_id', $session->id)
                ->where('user_id', $user->id)
                ->where('wants_bonus', true)
                ->whereNotNull('gps_validated_at')
                ->latest()
                ->first();

            if ($bonusEnCours) {
                $bonusQuery = Enigme::where('lieu_id', $bonusEnCours->enigme->lieu_id)
                    ->where('is_bonus', true)
                    ->whereDoesntHave('tentatives', function($q) use ($user) {
                        $q->where('user_id', $user->id)->where('succes', true);
                    })
                    ->orderBy('ordre');
                
                $nextBonus = $bonusQuery->first();
                if ($nextBonus) {
                    $enigme = $nextBonus;
                }
            }
        }

        // 3. Sinon, on ne choisit PAS d'énigme automatiquement
        if (!$enigme) {
            // On cherche le lieu le plus proche pour rediriger l'utilisateur vers son dashboard
            // afin qu'il puisse choisir son niveau d'énigme.
            $lieuQuery = Lieu::where('ville_id', $session->ville_id);
            
            if ($lat && $lng) {
                $lieuQuery->selectRaw('lieux.*, ( 6371 * acos( cos( radians(?) ) * cos( radians( lieux.latitude ) ) * cos( radians( lieux.longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( lieux.latitude ) ) ) ) AS distance', [$lat, $lng, $lat])
                          ->orderBy('distance');
            }
            
            $lieuProche = $lieuQuery->first();

            if ($lieuProche) {
                return redirect()->route('player.lieu.dashboard', [
                    'ville' => $session->ville_id,
                    'lieu' => $lieuProche->id
                ])->with('info', 'Choisissez votre niveau d\'énigme pour commencer !');
            }

            // Si vraiment aucun lieu trouvé (cas rare), on reste sur un fallback ou on cherche quand même une énigme
            $enigme = Enigme::whereHas('lieu', function($q) use ($session) {
                $q->where('ville_id', $session->ville_id);
            })->where('is_bonus', false)->orderBy('ordre')->first();
        }

        if ($enigme && $session->current_enigme_id !== $enigme->id) {
            $session->update(['current_enigme_id' => $enigme->id]);
        }

        $indicesDebloquesIds = [];
        $joueurScore = 0;
        $progression = null;

        if ($enigme) {
            $indicesDebloquesIds = IndiceDebloque::where('user_id', $user->id)
                ->where('session_jeu_id', $session->id)
                ->pluck('indice_id')
                ->toArray();

            $joueurSession = JoueurSession::where('user_id', $user->id)
                ->where('session_jeu_id', $session->id)
                ->first();
            
            $joueurScore = $joueurSession ? $joueurSession->score : 0;

            $progression = ProgressionEnigme::where('user_id', $user->id)
                ->where('session_jeu_id', $session->id)
                ->where('enigme_id', $enigme->id)
                ->first();
        }

        return Inertia::render('Player/Jeu', [
            'session' => $session->load('ville'),
            'enigme' => $enigme,
            'indices_debloques' => $indicesDebloquesIds,
            'joueur_score' => $joueurScore,
            'progression' => $progression,
        ]);
    }

    public function map(Request $request)
    {
        $user = auth()->user();
        $lieuId = $request->query('lieu');
        $lieu = $lieuId ? Lieu::with('ville')->find($lieuId) : null;

        // Récupérer les lieux validés (où l'utilisateur a réussi au moins une énigme)
        $lieuxValides = Lieu::whereHas('enigmes.tentatives', function($query) use ($user) {
            $query->where('user_id', $user->id)->where('succes', true);
        })->get();

        return Inertia::render('Player/Carte', [
            'lieu_actif' => $lieu,
            'lieux_valides' => $lieuxValides,
            'ville' => $lieu ? $lieu->ville : null,
        ]);
    }

    public function enigmes(Request $request)
    {
        $lieuId = $request->query('lieu');
        $lieu = $lieuId ? Lieu::findOrFail($lieuId) : null;

        return Inertia::render('Player/Enigme', [
            'lieu' => $lieu,
            'enigmes' => $lieu ? $lieu->enigmes()->with('indices')->orderBy('ordre')->get() : [],
        ]);
    }

    public function leaderboard(Request $request)
    {
        // On récupère le score total par utilisateur en groupant les JoueurSession
        $topJoueurs = \App\Models\User::select('users.id', 'users.name')
            ->leftJoin('joueur_sessions', 'users.id', '=', 'joueur_sessions.user_id')
            ->selectRaw('COALESCE(SUM(joueur_sessions.score), 0) as total_score')
            ->selectRaw('COUNT(DISTINCT joueur_sessions.session_jeu_id) as sessions_jouees')
            ->selectRaw('(SELECT COUNT(*) FROM tentatives_enigmes WHERE tentatives_enigmes.user_id = users.id AND succes = 1) as enigmes_resolues')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_score')
            ->take(20)
            ->get();

        return Inertia::render('Player/Leaderboard', [
            'top_joueurs' => $topJoueurs,
        ]);
    }

    public function websocket(Request $request)
    {
        $lieuId = $request->query('lieu');
        $lieu = $lieuId ? Lieu::findOrFail($lieuId) : null;

        return Inertia::render('Player/Websocket', [
            'lieu' => $lieu,
        ]);
    }

    /**
     * Détecter la ville la plus proche via AJAX.
     */
    public function detecterVille(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $ville = $this->cityService->trouverVilleProche(
            (float) $request->lat,
            (float) $request->lng
        );

        return response()->json([
            'success' => !!$ville,
            'ville' => $ville,
            'message' => $ville ? "Ville détectée : {$ville->nom}" : "Aucune ville enregistrée à proximité de votre position."
        ]);
    }

    /**
     * Démarrage automatique ou reprise d'une session depuis le menu principal.
     */
    public function autoStart(Request $request, SessionJeuService $sessionService)
    {
        $user = auth()->user();
        $villeId = $request->input('ville_id');
        $lat = $request->input('lat');
        $lng = $request->input('lng');

        // 1. Chercher une session active pour cet utilisateur (dans la ville si spécifiée, sinon n'importe où)
        $query = SessionJeu::whereHas('joueurs', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->whereIn('statut', ['actif', 'en_attente', 'pause', 'temps_epuise']);

        if ($villeId) {
            $query->where('ville_id', $villeId);
        }

        $session = $query->latest('updated_at')->first();

        // 2. Si une session existe, on la reprend
        if ($session) {
            if ($session->statut === 'en_attente') {
                $sessionService->commencerSession($session);
            } elseif ($session->statut === 'pause' || $session->statut === 'temps_epuise') {
                $sessionService->reprendreSession($session);
            }
            return redirect()->route('player.game.jeu', ['session' => $session->id, 'lat' => $lat, 'lng' => $lng]);
        }

        // 3. Si aucune session et on a une ville_id, on en crée une nouvelle
        if ($villeId) {
            $session = $sessionService->creerSession($user, [
                'ville_id' => $villeId,
                'mode' => 'cooperatif', // Mode par défaut pour l'auto-start
                'duree' => $request->input('duree', 45), // Utiliser la durée choisie ou 45 par défaut
            ]);
            $sessionService->commencerSession($session);
            return redirect()->route('player.game.jeu', ['session' => $session->id, 'lat' => $lat, 'lng' => $lng]);
        }

        // 4. Sinon, impossible de démarrer
        return back()->with('error', 'Impossible de démarrer : aucune ville détectée ou session active.');
    }
}
