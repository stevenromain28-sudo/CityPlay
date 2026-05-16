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

        $estValide = $this->gpsService->validerPosition(
            $request->latitude,
            $request->longitude,
            $enigme->latitude,
            $enigme->longitude,
            $enigme->rayon ?? 50 // Rayon par défaut de 50m
        );

        if ($estValide) {
            return $this->marquerEnigmeCommeResolue($session, $enigme, $request->user());
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
            return $this->marquerEnigmeCommeResolue($session, $enigme, $request->user());
        }

        return response()->json([
            'success' => false,
            'message' => 'Réponse incorrecte, réessayez !'
        ], 422);
    }

    /**
     * Logique interne pour marquer une énigme comme résolue.
     */
    protected function marquerEnigmeCommeResolue(SessionJeu $session, Enigme $enigme, $user)
    {
        // Enregistrer la tentative réussie
        TentativeEnigme::create([
            'enigme_id' => $enigme->id,
            'user_id' => $user->id,
            'succes' => true,
            'tente_le' => now(),
        ]);

        // Déclencher l'événement (qui calculera le score via le Listener)
        event(new EnigmeResolue($session, $enigme, $user));

        return response()->json([
            'success' => true,
            'message' => 'Félicitations ! Énigme résolue.',
            'content' => $enigme->lieu->contenuCulturel // On renvoie le contenu culturel débloqué
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
