<?php

namespace App\Http\Controllers;

use App\Models\Ville;
use App\Models\Lieu;
use App\Models\Enigme;
use App\Models\SessionJeu;
use App\Models\JoueurSession;
use App\Models\TentativeEnigme;
use App\Services\CityService;
use App\Services\SessionJeuService;
use Inertia\Inertia;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    protected $cityService;

    public function __construct(CityService $cityService)
    {
        $this->cityService = $cityService;
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
        
        // On cherche les énigmes actives pour cette session qui ne sont pas encore réussies
        $query = Enigme::whereHas('lieu', function($q) use ($session) {
            $q->where('ville_id', $session->ville_id);
        })
        ->whereDoesntHave('tentatives', function($q) use ($user) {
            $q->where('user_id', $user->id)->where('succes', true);
        })
        ->with('indices');

        // Si on a les coordonnées du joueur, on ordonne par distance (formule de Haversine via MySQL)
        if ($lat && $lng) {
            // S'assure de sélectionner toutes les colonnes d'Enigme et ajoute la distance
            $query->selectRaw('enigmes.*, ( 6371 * acos( cos( radians(?) ) * cos( radians( enigmes.latitude ) ) * cos( radians( enigmes.longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( enigmes.latitude ) ) ) ) AS distance', [$lat, $lng, $lat])
                  ->orderBy('distance')
                  ->orderBy('ordre');
        } else {
            $query->orderBy('ordre');
        }

        $enigme = $query->first();

        $indicesDebloquesIds = [];
        $joueurScore = 0;

        if ($enigme) {
            $indicesDebloquesIds = \App\Models\IndiceDebloque::where('user_id', $user->id)
                ->where('session_jeu_id', $session->id)
                ->pluck('indice_id')
                ->toArray();

            $joueurSession = \App\Models\JoueurSession::where('user_id', $user->id)
                ->where('session_jeu_id', $session->id)
                ->first();
            
            $joueurScore = $joueurSession ? $joueurSession->score : 0;
        }

        return Inertia::render('Player/Jeu', [
            'session' => $session->load('ville'),
            'enigme' => $enigme,
            'indices_debloques' => $indicesDebloquesIds,
            'joueur_score' => $joueurScore,
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

        // 1. Chercher une session active pour cet utilisateur (dans la ville si spécifiée, sinon n'importe où)
        $query = SessionJeu::whereHas('joueurs', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->whereIn('statut', ['actif', 'en_attente', 'pause']);

        if ($villeId) {
            $query->where('ville_id', $villeId);
        }

        $session = $query->latest('updated_at')->first();

        // 2. Si une session existe, on la reprend
        if ($session) {
            if ($session->statut === 'en_attente' || $session->statut === 'pause') {
                $sessionService->reprendreSession($session);
                if ($session->statut === 'en_attente') {
                    $sessionService->commencerSession($session);
                }
            }
            return redirect()->route('player.game.jeu', $session->id);
        }

        // 3. Si aucune session et on a une ville_id, on en crée une nouvelle
        if ($villeId) {
            $session = $sessionService->creerSession($user, [
                'ville_id' => $villeId,
                'mode' => 'cooperatif' // Par défaut
            ]);
            $sessionService->commencerSession($session);
            
            return redirect()->route('player.game.jeu', $session->id);
        }

        // 4. Sinon, impossible de démarrer
        return back()->with('error', 'Impossible de démarrer : aucune ville détectée ou session active.');
    }
}
