<?php

namespace App\Http\Controllers;

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

    public function __construct(GPSService $gpsService, ScoreService $scoreService)
    {
        $this->gpsService = $gpsService;
        $this->scoreService = $scoreService;
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

        // Si c'est une énigme bonus, on donne direct les points bonus et on finit
        if ($enigme->is_bonus) {
            $scoreGagne = $this->scoreService->calculerBonus($enigme);
            
            $joueurSession = JoueurSession::where('session_jeu_id', $session->id)
                ->where('user_id', $user->id)
                ->first();
            
            if ($joueurSession) {
                $joueurSession->increment('score', $scoreGagne);
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
                'score_gagne' => $scoreGagne
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

        return response()->json([
            'success' => true,
            'message' => 'Mystère résolu ! Le lieu est révélé. Maintenant, rendez-vous sur place !',
            'revealed' => true,
            'score_gagne' => $scoreGagne,
            'lieu' => [
                'nom' => $enigme->lieu->nom,
                'image' => $enigme->lieu->image ?? $enigme->image
            ]
        ]);
    }

    protected function marquerGPSCommeValide(SessionJeu $session, Enigme $enigme, $user, $progression)
    {
        $progression->update(['gps_validated_at' => now()]);

        // Score partiel (60%)
        $scoreGagne = $this->scoreService->calculerScoreEnigme($enigme, 0, 0, 'gps');

        $joueurSession = JoueurSession::where('session_jeu_id', $session->id)
            ->where('user_id', $user->id)
            ->first();
        
        if ($joueurSession) {
            $joueurSession->increment('score', $scoreGagne);
            $joueurSession->increment('progression');
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

        return response()->json([
            'success' => true,
            'message' => 'Félicitations ! Vous avez gagné tous les points de ce lieu.',
            'gps_validated' => true,
            'score_gagne' => $scoreGagne,
            'content' => $enigme->lieu->contenuCulturel,
            'show_choice' => true
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

            return response()->json([
                'success' => true,
                'message' => 'Super ! Voici vos énigmes bonus pour mieux connaître ce lieu.',
                'bonus_enigmes' => $bonusEnigmes
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'En route pour le prochain lieu !',
            'next_location' => true
        ]);
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
