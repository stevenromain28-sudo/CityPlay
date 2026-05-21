<?php

namespace App\Http\Controllers;

use Inertia\Inertia; 
use App\Models\Enigme;
use App\Models\SessionJeu;
use App\Models\TentativeEnigme;
use App\Services\GPSService;
use App\Services\ScoreService;
use App\Events\EnigmeResolue;
use App\Models\Indice;
use App\Models\IndiceDebloque;
use App\Models\JoueurSession;
use App\Models\ProgressionEnigme;
use Illuminate\Http\Request;

class GameplayController extends Controller
{
    protected $gpsService;
    protected $scoreService;
    protected $sessionJeuService;

    public function __construct(GPSService $gpsService, ScoreService $scoreService, \App\Services\SessionJeuService $sessionJeuService)
    {
        $this->gpsService = $gpsService;
        $this->scoreService = $scoreService;
        $this->sessionJeuService = $sessionJeuService;
    }

    /**
     * Commencer une session en attente.
     */
    public function commencerSession(Request $request, SessionJeu $session)
    {
        $user = $request->user();

        if ($session->statut === 'en_attente') {
            $this->sessionJeuService->commencerSession($session);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Valider la position GPS du joueur pour une énigme.
     */
    public function validerGPS(Request $request, SessionJeu $session, Enigme $enigme)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $user = $request->user();
        
        // Vérifier si la validation textuelle a été faite
        $progression = ProgressionEnigme::where('session_jeu_id', $session->id)
            ->where('user_id', $user->id)
            ->where('enigme_id', $enigme->id)
            ->first();

        if (!$progression || !$progression->text_validated_at) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez d\'abord résoudre le mystère textuel !'
            ], 422);
        }

        $estValide = $this->gpsService->validerPosition(
            $request->latitude,
            $request->longitude,
            $enigme->latitude,
            $enigme->longitude,
            $enigme->rayon ?? 50
        );

        if ($estValide) {
            return $this->marquerGPSCommeValide($session, $enigme, $user, $progression);
        }

        return response()->json([
            'success' => false,
            'message' => 'Vous n\'êtes pas encore assez proche du lieu !'
        ], 422);
    }

    /**
     * Soumettre une réponse textuelle à une énigme.
     */
    public function soumettreReponse(Request $request, SessionJeu $session, Enigme $enigme)
    {
        $request->validate([
            'reponse' => 'required|string',
        ]);

        $user = $request->user();

        // Vérifier si déjà validé
        $progression = ProgressionEnigme::firstOrCreate([
            'session_jeu_id' => $session->id,
            'user_id' => $user->id,
            'enigme_id' => $enigme->id,
        ]);

        if ($progression->text_validated_at) {
            return response()->json([
                'success' => true,
                'message' => 'Déjà validé !',
                'revealed' => true
            ]);
        }

        $motsCles = array_map('trim', explode(',', strtolower($enigme->reponse)));
        $reponseJoueur = strtolower(trim($request->reponse));

        $valide = false;
        foreach ($motsCles as $mot) {
            if ($mot !== '' && str_contains($reponseJoueur, $mot)) {
                $valide = true;
                break;
            }
        }

        if ($valide) {
            return $this->marquerTextCommeValide($session, $enigme, $user, $progression);
        }

        return response()->json([
            'success' => false,
            'message' => 'Réponse incorrecte, réessayez !'
        ], 422);
    }

    protected function marquerTextCommeValide(SessionJeu $session, Enigme $enigme, $user, $progression)
    {
        $progression->update(['text_validated_at' => now()]);

        // Si l'utilisateur est dans une équipe, partager la progression avec toute l'équipe
        $equipe = $user->equipe;
        if ($equipe) {
            // Créer/mettre à jour la progression pour l'équipe
            ProgressionEnigme::firstOrCreate(
                [
                    'equipe_id' => $equipe->id,
                    'session_jeu_id' => $session->id,
                    'enigme_id' => $enigme->id,
                ],
                ['user_id' => $user->id]
            )->update(['text_validated_at' => now()]);

            // Pour chaque membre de l'équipe, créer sa propre progression si elle n'existe pas
            foreach ($equipe->membres as $membre) {
                if ($membre->id !== $user->id) {
                    $progressionMembre = ProgressionEnigme::firstOrCreate(
                        [
                            'session_jeu_id' => $session->id,
                            'user_id' => $membre->id,
                            'enigme_id' => $enigme->id,
                        ]
                    );
                    if (!$progressionMembre->text_validated_at) {
                        $progressionMembre->update(['text_validated_at' => now()]);
                    }
                }
            }
        }

        // Si c'est une énigme bonus, on donne direct les points bonus et on finit
        if ($enigme->is_bonus) {
            $scoreGagne = $this->scoreService->calculerBonus($enigme);
            
            $joueurSession = JoueurSession::where('session_jeu_id', $session->id)
                ->where('user_id', $user->id)
                ->first();
            
            if ($joueurSession) {
                $joueurSession->increment('score', $scoreGagne);
            }

            // Si en équipe, partager les points avec l'équipe
            if ($equipe) {
                $equipe->increment('score_total', $scoreGagne);
                foreach ($equipe->membres as $membre) {
                    if ($membre->id !== $user->id) {
                        $jsMembre = JoueurSession::where('session_jeu_id', $session->id)
                            ->where('user_id', $membre->id)
                            ->first();
                        if ($jsMembre) {
                            $jsMembre->increment('score', $scoreGagne);
                        }
                    }
                }
            }

            // Enregistrer la tentative réussie pour le bonus
            TentativeEnigme::create([
                'enigme_id' => $enigme->id,
                'user_id' => $user->id,
                'succes' => true,
                'tente_le' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bonus validé ! +' . $scoreGagne . ' XP',
                'is_bonus' => true,
                'score_gagne' => $scoreGagne,
                'equipe' => $equipe
            ]);
        }

        // Score partiel (40%) pour énigme classique
        $scoreGagne = $this->scoreService->calculerScoreEnigme($enigme, 0, 0, 'text');
        
        $joueurSession = JoueurSession::where('session_jeu_id', $session->id)
            ->where('user_id', $user->id)
            ->first();
        
        if ($joueurSession) {
            $joueurSession->increment('score', $scoreGagne);
        }

        // Si en équipe, partager les points avec l'équipe
        if ($equipe) {
            $equipe->increment('score_total', $scoreGagne);
            foreach ($equipe->membres as $membre) {
                if ($membre->id !== $user->id) {
                    $jsMembre = JoueurSession::where('session_jeu_id', $session->id)
                        ->where('user_id', $membre->id)
                        ->first();
                    if ($jsMembre) {
                        $jsMembre->increment('score', $scoreGagne);
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Mystère résolu ! Le lieu est révélé. Maintenant, rendez-vous sur place !',
            'revealed' => true,
            'score_gagne' => $scoreGagne,
            'lieu' => [
                'nom' => $enigme->lieu->nom,
                'image' => $enigme->lieu->image ?? $enigme->image
            ],
            'equipe' => $equipe
        ]);
    }

    protected function marquerGPSCommeValide(SessionJeu $session, Enigme $enigme, $user, $progression)
    {
        $progression->update(['gps_validated_at' => now()]);

        // Si l'utilisateur est dans une équipe, partager la progression avec toute l'équipe
        $equipe = $user->equipe;
        if ($equipe) {
            // Mettre à jour la progression de l'équipe
            $progressionEquipe = ProgressionEnigme::where('equipe_id', $equipe->id)
                ->where('session_jeu_id', $session->id)
                ->where('enigme_id', $enigme->id)
                ->first();
            if ($progressionEquipe) {
                $progressionEquipe->update(['gps_validated_at' => now()]);
            }

            // Pour chaque membre de l'équipe, mettre à jour sa progression
            foreach ($equipe->membres as $membre) {
                if ($membre->id !== $user->id) {
                    $progressionMembre = ProgressionEnigme::where('session_jeu_id', $session->id)
                        ->where('user_id', $membre->id)
                        ->where('enigme_id', $enigme->id)
                        ->first();
                    if ($progressionMembre && !$progressionMembre->gps_validated_at) {
                        $progressionMembre->update(['gps_validated_at' => now()]);
                    }
                }
            }
        }

        // Score partiel (60%)
        $scoreGagne = $this->scoreService->calculerScoreEnigme($enigme, 0, 0, 'gps');

        $joueurSession = JoueurSession::where('session_jeu_id', $session->id)
            ->where('user_id', $user->id)
            ->first();
        
        if ($joueurSession) {
            $joueurSession->increment('score', $scoreGagne);
            $joueurSession->increment('progression');
        }

        // Si en équipe, partager les points et la progression avec l'équipe
        if ($equipe) {
            $equipe->increment('score_total', $scoreGagne);
            foreach ($equipe->membres as $membre) {
                if ($membre->id !== $user->id) {
                    $jsMembre = JoueurSession::where('session_jeu_id', $session->id)
                        ->where('user_id', $membre->id)
                        ->first();
                    if ($jsMembre) {
                        $jsMembre->increment('score', $scoreGagne);
                        $jsMembre->increment('progression');
                    }
                }
            }
        }

        // On libère l'énigme courante de la session
        $session->update(['current_enigme_id' => null]);

        // Enregistrer la tentative réussie globale
        TentativeEnigme::create([
            'enigme_id' => $enigme->id,
            'user_id' => $user->id,
            'succes' => true,
            'tente_le' => now(),
        ]);

        $message = 'Félicitations ! Vous avez gagné tous les points de ce lieu.';
        if ($enigme->lieu && $enigme->lieu->contenuCulturel) {
            $message .= ' 📖 Un nouveau savoir historique a été gravé dans votre Grimoire ! Allez vite le consulter dans l\'Historique Culturel.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'gps_validated' => true,
            'score_gagne' => $scoreGagne,
            'show_choice' => true,
            'equipe' => $equipe
        ]);
    }


    /**
     * Gérer le choix après validation GPS.
     */
    public function faireChoixBonus(Request $request, SessionJeu $session, Enigme $enigme)
    {
        $request->validate(['wants_bonus' => 'required|boolean']);
        $user = $request->user();

        $progression = ProgressionEnigme::where('session_jeu_id', $session->id)
            ->where('user_id', $user->id)
            ->where('enigme_id', $enigme->id)
            ->first();

        if ($progression) {
            $progression->update([
                'bonus_choice_made' => true,
                'wants_bonus' => $request->wants_bonus
            ]);
        }

        if ($request->wants_bonus) {
            // Récupérer les énigmes bonus pour ce lieu
            $bonusEnigmes = Enigme::where('lieu_id', $enigme->lieu_id)
                ->where('is_bonus', true)
                ->orderBy('ordre')
                ->get();

            $nextBonus = $bonusEnigmes->first();
            if ($nextBonus) {
                $session->update(['current_enigme_id' => $nextBonus->id]);
            }

            if ($request->inertia()) {
                return redirect()->back()->with('success', 'Super ! Voici vos énigmes bonus pour mieux connaître ce lieu.');
            }

            return response()->json([
                'success' => true,
                'message' => 'Super ! Voici vos énigmes bonus pour mieux connaître ce lieu.',
                'bonus_enigmes' => $bonusEnigmes
            ]);
        }

        // Si wants_bonus est false : trouver le prochain lieu non complété et rediriger vers lieu.dashboard !
        $equipe = $user->equipe;
        
        // 1. Déterminer quels lieux ont déjà été complétés
        $lieuxDejaCompletes = collect();
        if ($equipe) {
            $lieuxDejaCompletes = \App\Models\Lieu::where('ville_id', $session->ville_id)
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
            $lieuxDejaCompletes = \App\Models\Lieu::where('ville_id', $session->ville_id)
                ->whereHas('enigmes', function ($q) use ($user) {
                    $q->where('is_bonus', false)
                      ->whereHas('tentatives', function ($q2) use ($user) {
                          $q2->where('user_id', $user->id)->where('succes', true);
                      });
                })
                ->pluck('id');
        }

        // 2. Chercher le prochain lieu non complété
        $lieuProche = \App\Models\Lieu::where('ville_id', $session->ville_id)
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
            // Tous les lieux sont complétés : rediriger vers la page dédiée
            return Inertia::render('Player/TousLieuxVisites', [
                'session' => $session->load('ville'),
                'equipe' => $equipe,
            ]);
        }
    }

    /**
     * Débloquer un indice pour une énigme.
     */
    public function debloquerIndice(Request $request, SessionJeu $session, Enigme $enigme, Indice $indice)
    {
        $user = $request->user();

        // Vérifier si l'indice appartient bien à l'énigme
        if ($indice->enigme_id !== $enigme->id) {
            return response()->json(['success' => false, 'message' => 'Indice invalide.'], 403);
        }

        // Vérifier si l'indice est déjà débloqué
        $dejaDebloque = IndiceDebloque::where('user_id', $user->id)
            ->where('session_jeu_id', $session->id)
            ->where('indice_id', $indice->id)
            ->exists();

        if ($dejaDebloque) {
            return response()->json([
                'success' => true,
                'message' => 'Indice déjà débloqué',
                'contenu' => $indice->contenu
            ]);
        }

        // Vérifier si c'est le premier indice débloqué pour cette énigme (gratuit)
        $indicesDeLEnigme = $enigme->indices()->pluck('id');
        $nbIndicesDebloques = IndiceDebloque::where('user_id', $user->id)
            ->where('session_jeu_id', $session->id)
            ->whereIn('indice_id', $indicesDeLEnigme)
            ->count();

        $cout = $nbIndicesDebloques === 0 ? 0 : $indice->penalite;

        $joueurSession = JoueurSession::where('user_id', $user->id)
            ->where('session_jeu_id', $session->id)
            ->first();

        if (!$joueurSession) {
            return response()->json(['success' => false, 'message' => 'Session introuvable pour ce joueur.'], 404);
        }

        if ($cout > 0 && $joueurSession->score < $cout) {
            return response()->json(['success' => false, 'message' => "Fonds insuffisants. Il vous faut $cout XP."], 403);
        }

        // Déduire les points
        if ($cout > 0) {
            $joueurSession->score -= $cout;
            $joueurSession->save();
        }

        // Enregistrer le déblocage
        IndiceDebloque::create([
            'user_id' => $user->id,
            'session_jeu_id' => $session->id,
            'indice_id' => $indice->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => $cout > 0 ? "-$cout XP utilisés" : 'Indice gratuit débloqué !',
            'contenu' => $indice->contenu,
            'nouveau_score' => $joueurSession->score
        ]);
    }
}
