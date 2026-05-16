<?php

namespace App\Http\Controllers;

use App\Models\Enigme;
use App\Models\SessionJeu;
use App\Models\TentativeEnigme;
use App\Services\GPSService;
use App\Services\ScoreService;
use App\Events\EnigmeResolue;
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

        if (strtolower($request->reponse) === strtolower($enigme->reponse)) {
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
}
