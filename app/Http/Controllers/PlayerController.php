<?php

namespace App\Http\Controllers;

use App\Models\Ville;
use App\Models\Lieu;
use App\Models\Enigme;
use App\Models\SessionJeu;
use App\Models\JoueurSession;
use App\Models\TentativeEnigme;
use App\Models\Invitation;
use App\Models\ProgressionEnigme;
use App\Models\IndiceDebloque;
use App\Services\CityService;
use App\Services\SessionJeuService;
use App\Services\InvitationService;
use Inertia\Inertia;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    protected $cityService;
    protected $sessionService;
    protected $invitationService;

    public function __construct(CityService $cityService, SessionJeuService $sessionService, InvitationService $invitationService)
    {
        $this->cityService = $cityService;
        $this->sessionService = $sessionService;
        $this->invitationService = $invitationService;
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

        // Récupérer l'équipe du joueur
        $equipe = $user->equipe?->load('membres');

        // Récupérer la dernière invitation active du joueur
        $derniereInvitation = null;
        $lienInvitation = null;
        $derniereInvitation = Invitation::where('inviteur_id', $user->id)
            ->where('type', 'solo_equipe')
            ->latest()
            ->first();
        
        if ($derniereInvitation && $derniereInvitation->estValide()) {
            $lienInvitation = $this->invitationService->genererLienInvitation($derniereInvitation);
        }

        return Inertia::render('Player/Dashboard', [
            'stats' => $stats,
            'recent_sessions' => $sessions,
            'villes_disponibles' => Ville::where('actif', true)->get(),
            'ville_detectee' => $villeDetectee,
            'lieux' => $lieux,
            'localisation_requise' => !$latitude || !$longitude,
            'equipe' => $equipe,
            'lien_invitation' => $lienInvitation,
            'invitation' => $derniereInvitation,
        ]);
    }

    /**
     * Dashboard spécifique à un lieu.
     */
    public function lieuDashboard(Ville $ville, Lieu $lieu)
    {
        $user = auth()->user();
        $equipe = $user->equipe;
        $dejaComplete = false;
        $activeSession = null;

        // Vérifier si ce lieu a déjà été visité (au moins une énigme résolue)
        if ($equipe) {
            // Vérifier pour l'équipe : au moins une énigme non-bonus a été résolue
            $dejaComplete = $lieu->enigmes()
                ->where('is_bonus', false)
                ->whereHas('tentatives', function ($q) use ($equipe) {
                    $q->whereHas('user', function ($q2) use ($equipe) {
                        $q2->where('equipe_id', $equipe->id);
                    })->where('succes', true);
                })
                ->exists();
            
            // Chercher session active pour l'équipe
            $activeSession = \App\Models\SessionJeu::where('equipe_id', $equipe->id)
                ->where('ville_id', $ville->id)
                ->whereIn('statut', ['actif', 'en_attente', 'pause'])
                ->latest('updated_at')
                ->first();
        } else {
            // Vérifier pour le joueur individuel : au moins une énigme non-bonus a été résolue
            $dejaComplete = $lieu->enigmes()
                ->where('is_bonus', false)
                ->whereHas('tentatives', function ($q) use ($user) {
                    $q->where('user_id', $user->id)->where('succes', true);
                })
                ->exists();
            
            // Chercher session active pour le joueur
            $activeSession = \App\Models\SessionJeu::whereHas('joueurs', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->where('ville_id', $ville->id)
                ->whereIn('statut', ['actif', 'en_attente', 'pause'])
                ->latest('updated_at')
                ->first();
        }

        return Inertia::render('Player/LieuDashboard', [
            'ville' => $ville,
            'lieu' => $lieu->loadCount('enigmes'),
            'enigmes' => $lieu->enigmes()->orderBy('ordre')->get(),
            'deja_complete' => $dejaComplete,
            'equipe' => $equipe,
            'active_session' => $activeSession,
        ]);
    }

    /**
     * Définir l'énigme courante et rediriger vers le jeu.
     */
    public function choisirEnigme(Request $request, SessionJeu $session, Enigme $enigme)
    {
        $request->validate([]);
        
        // Vérifier que l'énigme appartient bien à la ville de la session
        if ($enigme->lieu->ville_id !== $session->ville_id) {
            abort(403, 'Cette énigme n\'appartient pas à cette session.');
        }

        // Définir l'énigme courante
        $session->update(['current_enigme_id' => $enigme->id]);

        // Récupérer les paramètres lat/lng
        $lat = $request->query('lat');
        $lng = $request->query('lng');

        // Rediriger vers la page de jeu
        return redirect()->route('player.game.jeu', [
            'session' => $session->id,
            'lat' => $lat,
            'lng' => $lng
        ]);
    }

    public function jeu(Request $request, SessionJeu $session)
    {
        $user = auth()->user();
        $lat = (float) $request->query('lat');
        $lng = (float) $request->query('lng');
        $equipe = $user->equipe;

        // Aucun recalcul : le frontend est le seul maître du temps
        
        $enigme = null;

        // 1. Vérifier si une énigme spécifique est déjà définie dans la session
        if ($session->current_enigme_id) {
            $enigme = Enigme::with('indices', 'lieu')->find($session->current_enigme_id);
            // Vérifier si elle est déjà résolue par le joueur OU par l'équipe
            $dejaResolue = false;
            if ($equipe) {
                $dejaResolue = TentativeEnigme::where('enigme_id', $session->current_enigme_id)
                    ->whereHas('user', function($q) use ($equipe) {
                        $q->where('equipe_id', $equipe->id);
                    })
                    ->where('succes', true)
                    ->exists();
            } else {
                $dejaResolue = TentativeEnigme::where('user_id', $user->id)
                    ->where('enigme_id', $session->current_enigme_id)
                    ->where('succes', true)
                    ->exists();
            }
            
            if ($dejaResolue) {
                $enigme = null; // On passera à la suite
            }
        }

        // 2. Vérifier si le joueur (ou équipe) a des bonus en cours
        if (!$enigme) {
            if ($equipe) {
                $bonusEnCours = ProgressionEnigme::where('session_jeu_id', $session->id)
                    ->where('equipe_id', $equipe->id)
                    ->where('wants_bonus', true)
                    ->whereNotNull('gps_validated_at')
                    ->latest()
                    ->first();
            } else {
                $bonusEnCours = ProgressionEnigme::where('session_jeu_id', $session->id)
                    ->where('user_id', $user->id)
                    ->where('wants_bonus', true)
                    ->whereNotNull('gps_validated_at')
                    ->latest()
                    ->first();
            }

            if ($bonusEnCours) {
                if ($equipe) {
                    $bonusQuery = Enigme::with('lieu', 'indices')
                        ->where('lieu_id', $bonusEnCours->enigme->lieu_id)
                        ->where('is_bonus', true)
                        ->whereDoesntHave('tentatives', function($q) use ($equipe) {
                            $q->whereHas('user', function($q2) use ($equipe) {
                                $q2->where('equipe_id', $equipe->id);
                            })->where('succes', true);
                        })
                        ->orderBy('ordre');
                } else {
                    $bonusQuery = Enigme::with('lieu', 'indices')
                        ->where('lieu_id', $bonusEnCours->enigme->lieu_id)
                        ->where('is_bonus', true)
                        ->whereDoesntHave('tentatives', function($q) use ($user) {
                            $q->where('user_id', $user->id)->where('succes', true);
                        })
                        ->orderBy('ordre');
                }
                
                $nextBonus = $bonusQuery->first();
                if ($nextBonus) {
                    $enigme = $nextBonus;
                }
            }
        }

        // 3. Déterminer quels lieux ont déjà été complétés par le joueur/équipe (indispensable pour la suite et pour les autres lieux)
        $lieuxDejaCompletes = collect();
        if ($equipe) {
            $lieuxDejaCompletes = Lieu::where('ville_id', $session->ville_id)
                ->whereHas('enigmes', function ($q) use ($equipe) {
                    $q->where('is_bonus', false)
                      ->whereHas('tentatives', function ($q2) use ($equipe) {
                          $q2->whereHas('user', function ($q3) use ($equipe) {
                              $q3->where('equipe_id', $equipe->id);
                          })->where('succes', true);
                      });
                })
                ->pluck('id');
        } else {
            $lieuxDejaCompletes = Lieu::where('ville_id', $session->ville_id)
                ->whereHas('enigmes', function ($q) use ($user) {
                    $q->where('is_bonus', false)
                      ->whereHas('tentatives', function ($q2) use ($user) {
                          $q2->where('user_id', $user->id)->where('succes', true);
                      });
                })
                ->pluck('id');
        }

        // 4. Si pas d'énigme : trouver le prochain lieu non complété et rediriger vers lieu.dashboard !
        if (!$enigme) {
            $lieuProche = Lieu::where('ville_id', $session->ville_id)
                ->whereNotIn('id', $lieuxDejaCompletes)
                ->first();

            if ($lieuProche) {
                // Réinitialiser l'énigme courante de la session
                $session->update(['current_enigme_id' => null]);
                
                // Rediriger vers la page LieuDashboard pour choisir l'énigme !
                return redirect()->route('player.lieu.dashboard', [
                    'ville' => $session->ville_id,
                    'lieu' => $lieuProche->id
                ]);
            } else {
                // Tous les lieux sont complétés !
                return Inertia::render('Player/TousLieuxVisites', [
                    'session' => $session->load('ville'),
                    'equipe' => $equipe,
                ]);
            }
        }

        if ($enigme && $session->current_enigme_id !== $enigme->id) {
            $session->update(['current_enigme_id' => $enigme->id]);
        }

        $indicesDebloquesIds = [];
        $joueurScore = 0;
        $progression = null;

        if ($enigme) {
            if ($equipe) {
                // Si en équipe, tous les indices débloqués par n'importe quel membre sont disponibles pour tous
                $indicesDebloquesIds = IndiceDebloque::whereHas('user', function($q) use ($equipe) {
                        $q->where('equipe_id', $equipe->id);
                    })
                    ->where('session_jeu_id', $session->id)
                    ->pluck('indice_id')
                    ->toArray();
                
                // Utiliser le score de l'équipe (via le JoueurSession du joueur)
                $joueurSession = JoueurSession::where('user_id', $user->id)
                    ->where('session_jeu_id', $session->id)
                    ->first();
                
                $joueurScore = $joueurSession ? $joueurSession->score : 0;

                // Utiliser la progression de l'équipe si existante, sinon la progression individuelle
                $progression = ProgressionEnigme::where('equipe_id', $equipe->id)
                    ->where('session_jeu_id', $session->id)
                    ->where('enigme_id', $enigme->id)
                    ->first();
                
                if (!$progression) {
                    $progression = ProgressionEnigme::where('user_id', $user->id)
                        ->where('session_jeu_id', $session->id)
                        ->where('enigme_id', $enigme->id)
                        ->first();
                }
            } else {
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
        }

        // Récupérer les autres lieux non complétés pour pouvoir passer de l'un à l'autre
        $autresLieux = [];
        if ($enigme) {
            $autresLieux = Lieu::where('ville_id', $session->ville_id)
                ->whereNotIn('id', $lieuxDejaCompletes)
                ->where('id', '!=', $enigme->lieu_id)
                ->get();
        }

        return Inertia::render('Player/Jeu', [
            'session' => $session->load('ville'),
            'enigme' => $enigme,
            'indices_debloques' => $indicesDebloquesIds,
            'joueur_score' => $joueurScore,
            'progression' => $progression,
            'autres_lieux' => $autresLieux,
        ]);
    }

    public function changerLieu(Request $request, SessionJeu $session)
    {
        $user = auth()->user();
        $equipe = $user->equipe;

        // Déterminer les lieux déjà complétés
        $lieuxDejaCompletes = collect();
        if ($equipe) {
            $lieuxDejaCompletes = Lieu::where('ville_id', $session->ville_id)
                ->whereHas('enigmes', function ($q) use ($equipe) {
                    $q->where('is_bonus', false)
                      ->whereHas('tentatives', function ($q2) use ($equipe) {
                          $q2->whereHas('user', function ($q3) use ($equipe) {
                              $q3->where('equipe_id', $equipe->id);
                          })->where('succes', true);
                      });
                })
                ->pluck('id');
        } else {
            $lieuxDejaCompletes = Lieu::where('ville_id', $session->ville_id)
                ->whereHas('enigmes', function ($q) use ($user) {
                    $q->where('is_bonus', false)
                      ->whereHas('tentatives', function ($q2) use ($user) {
                          $q2->where('user_id', $user->id)->where('succes', true);
                      });
                })
                ->pluck('id');
        }

        // Trouver le lieu actuel
        $currentEnigme = Enigme::find($session->current_enigme_id);
        $currentLieu = $currentEnigme ? $currentEnigme->lieu : null;

        // Trouver tous les autres lieux non complétés
        $autresLieux = Lieu::where('ville_id', $session->ville_id)
            ->whereNotIn('id', $lieuxDejaCompletes);

        if ($currentLieu) {
            $autresLieux = $autresLieux->where('id', '!=', $currentLieu->id);
        }

        $autresLieux = $autresLieux->get();

        if ($autresLieux->isEmpty()) {
            return redirect()->route('player.game.jeu', ['session' => $session->id])
                ->with('error', 'Aucun autre lieu disponible.');
        }

        // Trouver le lieu le plus proche du lieu actuel (si on a des coordonnées valides)
        $lieuCible = null;
        if ($currentLieu && $currentLieu->latitude && $currentLieu->longitude) {
            $minDistance = null;
            foreach ($autresLieux as $l) {
                if ($l->latitude && $l->longitude) {
                    $dist = $this->calculerDistance($currentLieu->latitude, $currentLieu->longitude, $l->latitude, $l->longitude);
                    if ($minDistance === null || $dist < $minDistance) {
                        $minDistance = $dist;
                        $lieuCible = $l;
                    }
                }
            }
        }

        // Si aucun lieu cible n'est trouvé via coordonnées, on prend le premier
        if (!$lieuCible) {
            $lieuCible = $autresLieux->first();
        }

        // Trouver la première énigme non bonus de ce nouveau lieu cible
        $nouvelleEnigme = Enigme::where('lieu_id', $lieuCible->id)
            ->where('is_bonus', false)
            ->orderBy('ordre')
            ->first();

        if ($nouvelleEnigme) {
            $session->update(['current_enigme_id' => $nouvelleEnigme->id]);
        } else {
            $session->update(['current_enigme_id' => null]);
        }

        return redirect()->route('player.lieu.dashboard', [
            'ville' => $session->ville_id,
            'lieu' => $lieuCible->id
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
        // On récupère le score total par utilisateur EN EXCLUANT les joueurs en équipe
        $topJoueurs = \App\Models\User::select('users.id', 'users.name')
            ->leftJoin('joueur_sessions', 'users.id', '=', 'joueur_sessions.user_id')
            ->selectRaw('COALESCE(SUM(joueur_sessions.score), 0) as total_score')
            ->selectRaw('COUNT(DISTINCT joueur_sessions.session_jeu_id) as sessions_jouees')
            ->selectRaw('(SELECT COUNT(*) FROM tentatives_enigmes WHERE tentatives_enigmes.user_id = users.id AND succes = 1) as enigmes_resolues')
            ->whereNull('users.equipe_id') // EXCLUER les joueurs en équipe
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_score')
            ->take(20)
            ->get();

        // Récupérer aussi les équipes pour un classement séparé (optionnel)
        $topEquipes = \App\Models\Equipe::select('equipes.id', 'equipes.nom')
            ->leftJoin('users', 'equipes.id', '=', 'users.equipe_id')
            ->leftJoin('joueur_sessions', 'users.id', '=', 'joueur_sessions.user_id')
            ->selectRaw('COALESCE(SUM(joueur_sessions.score), 0) as total_score')
            ->groupBy('equipes.id', 'equipes.nom')
            ->orderByDesc('total_score')
            ->take(10)
            ->get();

        return Inertia::render('Player/Leaderboard', [
            'top_joueurs' => $topJoueurs,
            'top_equipes' => $topEquipes,
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
        $equipe = $user->equipe;
        $nouvelleSession = $request->input('nouvelle_session', false);
        $enigmeId = $request->input('enigme_id');

        // 1. Si l'utilisateur demande une NOUVELLE SESSION : terminer toutes les sessions existantes et nettoyer les données !
        if ($nouvelleSession) {
            // Terminer toutes les sessions de l'utilisateur (solo ou équipe)
            SessionJeu::whereHas('joueurs', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->whereIn('statut', ['actif', 'en_attente', 'pause', 'temps_epuise'])
              ->update(['statut' => 'termine', 'termine_le' => now()]);

            // Si en équipe, terminer aussi les sessions de l'équipe
            if ($equipe) {
                SessionJeu::where('equipe_id', $equipe->id)
                    ->whereIn('statut', ['actif', 'en_attente', 'pause', 'temps_epuise'])
                    ->update(['statut' => 'termine', 'termine_le' => now()]);
            }

            // --- NETTOYAGE DES DONNÉES DE JEU PRÉCÉDENTES ---
            // Supprimer les tentatives réussies pour que les énigmes redeviennent jouables
            TentativeEnigme::where('user_id', $user->id)->delete();
            
            // Supprimer les progressions (textuelles et GPS)
            ProgressionEnigme::where('user_id', $user->id)->delete();
            
            // Supprimer les indices débloqués
            IndiceDebloque::where('user_id', $user->id)->delete();

            if ($equipe) {
                // Si en équipe, nettoyer aussi les données liées à l'équipe
                ProgressionEnigme::where('equipe_id', $equipe->id)->delete();
                
                // Nettoyer les tentatives de tous les membres de l'équipe
                $membresIds = $equipe->membres->pluck('id');
                TentativeEnigme::whereIn('user_id', $membresIds)->delete();
                IndiceDebloque::whereIn('user_id', $membresIds)->delete();
            }
        }

        // 2. Si PAS de demande de nouvelle session : chercher une session active
        if (!$nouvelleSession) {
            // 2.a Si en équipe : chercher session équipe
            if ($equipe) {
                $queryEquipe = SessionJeu::where('equipe_id', $equipe->id)
                    ->whereIn('statut', ['actif', 'en_attente', 'pause']);
                
                if ($villeId) {
                    $queryEquipe->where('ville_id', $villeId);
                }
                
                $session = $queryEquipe->latest('updated_at')->first();

                if ($session) {
                    // Ajouter automatiquement l'utilisateur à la session si ce n'est pas déjà le cas
                    $sessionService->ajouterMembreEquipeASession($user, $session);

                    // Reprendre la session si nécessaire
                    if ($session->statut === 'en_attente' || $session->statut === 'pause') {
                        $sessionService->reprendreSession($session);
                        if ($session->statut === 'en_attente') {
                            $sessionService->commencerSession($session);
                        }
                    }
                    if ($enigmeId) {
                        return redirect()->route('player.game.choisir-enigme', ['session' => $session->id, 'enigme' => $enigmeId, 'lat' => $lat, 'lng' => $lng]);
                    }
                    return redirect()->route('player.game.jeu', ['session' => $session->id, 'lat' => $lat, 'lng' => $lng]);
                }
            }

            // 2.b Sinon : chercher session solo
            $query = SessionJeu::whereHas('joueurs', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->whereIn('statut', ['actif', 'en_attente', 'pause', 'temps_epuise']);

            if ($villeId) {
                $query->where('ville_id', $villeId);
            }

            $session = $query->latest('updated_at')->first();

            if ($session) {
                if ($session->statut === 'en_attente') {
                    $sessionService->commencerSession($session);
                } elseif ($session->statut === 'pause' || $session->statut === 'temps_epuise') {
                    $sessionService->reprendreSession($session);
                }
                if ($enigmeId) {
                    return redirect()->route('player.game.choisir-enigme', ['session' => $session->id, 'enigme' => $enigmeId, 'lat' => $lat, 'lng' => $lng]);
                }
                return redirect()->route('player.game.jeu', ['session' => $session->id, 'lat' => $lat, 'lng' => $lng]);
            }
        }

        // 3. Si on arrive ici : CREER une NOUVELLE SESSION !
        if ($villeId) {
            $data = [
                'ville_id' => $villeId,
                'mode' => 'cooperatif', // Par défaut
                'duree' => $request->input('duree', 45),
                'moyen_transport' => $request->input('moyen_transport', 'pied'),
            ];

            // Si dans une équipe, ajouter l'équipe à la session
            if ($equipe) {
                $data['equipe_id'] = $equipe->id;
            }

            $session = $sessionService->creerSession($user, $data);
            $sessionService->commencerSession($session);
            
            if ($enigmeId) {
                return redirect()->route('player.game.choisir-enigme', ['session' => $session->id, 'enigme' => $enigmeId, 'lat' => $lat, 'lng' => $lng]);
            }
            return redirect()->route('player.game.jeu', ['session' => $session->id, 'lat' => $lat, 'lng' => $lng]);
        }

        // 4. Sinon, impossible de démarrer
        return back()->with('error', 'Impossible de démarrer : aucune ville détectée ou session active.');
    }

    /**
     * Rejoindre directement le jeu pour une ville spécifique (lien d'invitation).
     */
    public function joinCity(Request $request, Ville $ville)
    {
        $user = auth()->user();
        $sessionService = new SessionJeuService();

        // 1. Chercher une session active pour cet utilisateur dans cette ville
        $session = SessionJeu::whereHas('joueurs', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->where('ville_id', $ville->id)
        ->whereIn('statut', ['actif', 'en_attente', 'pause'])
        ->latest('updated_at')
        ->first();

        // 2. Si une session existe, on la reprend
        if ($session) {
            if ($session->statut === 'en_attente' || $session->statut === 'pause') {
                $sessionService->reprendreSession($session);
                if ($session->statut === 'en_attente') {
                    $sessionService->commencerSession($session);
                }
            }
        } else {
            // 3. Sinon, on en crée une nouvelle
            $session = $sessionService->creerSession($user, [
                'ville_id' => $ville->id,
                'mode' => 'cooperatif'
            ]);
            $sessionService->commencerSession($session);
        }

        return redirect()->route('player.game.jeu', $session)
            ->with('success', "Bienvenue dans l'aventure à {$ville->nom} !");
    }

    /**
     * Historique des lieux complétés avec leur contenu culturel.
     */
    public function historiqueCulturel()
    {
        $user = auth()->user();
        $equipe = $user->equipe;

        // 1. Récupérer les IDs des lieux complétés (au moins 1 énigme non-bonus résolue)
        $lieuxCompletesIds = collect();
        
        if ($equipe) {
            // Pour équipe
            $lieuxCompletesIds = Lieu::whereHas('enigmes', function ($q) use ($equipe) {
                $q->where('is_bonus', false)
                  ->whereHas('tentatives', function ($q2) use ($equipe) {
                      $q2->whereHas('user', function ($q3) use ($equipe) {
                          $q3->where('equipe_id', $equipe->id);
                      })->where('succes', true);
                  });
            })->pluck('id');
        } else {
            // Pour joueur solo
            $lieuxCompletesIds = Lieu::whereHas('enigmes', function ($q) use ($user) {
                $q->where('is_bonus', false)
                  ->whereHas('tentatives', function ($q2) use ($user) {
                      $q2->where('user_id', $user->id)->where('succes', true);
                  });
            })->pluck('id');
        }

        // 2. Récupérer ces lieux avec leur contenu culturel et leur ville
        $lieuxCompletes = Lieu::whereIn('id', $lieuxCompletesIds)
            ->with(['contenuCulturel', 'ville'])
            ->get();

        return Inertia::render('Player/HistoriqueCulturel', [
            'lieux_completes' => $lieuxCompletes,
        ]);
    }

    private function calculerDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // en mètres
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $earthRadius * $c;
    }
}
