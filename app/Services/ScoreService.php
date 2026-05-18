<?php

namespace App\Services;

use App\Models\Enigme;
use App\Models\TentativeEnigme;

class ScoreService
{
    /**
     * Calcule le score pour une énigme résolue.
     */
    public function calculerScoreEnigme(Enigme $enigme, int $tempsEcoule, int $indicesUtilises): int
    {
        // Score de base selon la difficulté (niveau 1 -> 3)
        $scoreDeBase = $enigme->niveau * 1000;

        // Malus pour le temps (ex: -10 points par minute)
        $malusTemps = floor($tempsEcoule / 60) * 10;

        // Malus pour les indices (ex: -20% du score de base par indice)
        $malusIndices = ($scoreDeBase * 0.2) * $indicesUtilises;

        $scoreFinal = $scoreDeBase - $malusTemps - $malusIndices;

        // On s'assure que le score ne soit pas négatif, minimum 100 points pour l'effort
        return max(100, (int)$scoreFinal);
    }
}
