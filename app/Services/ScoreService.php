<?php

namespace App\Services;

use App\Models\Enigme;
use App\Models\TentativeEnigme;

class ScoreService
{
    /**
     * Calcule le score pour une énigme résolue.
     */
    public function calculerScoreEnigme(Enigme $enigme, int $tempsEcoule, int $indicesUtilises, string $type = 'full'): int
    {
        // Score de base selon la difficulté (niveau 1 -> 3)
        $scoreDeBase = $enigme->niveau * 1000;

        // Malus pour le temps (ex: -10 points par minute)
        $malusTemps = floor($tempsEcoule / 60) * 10;

        // Malus pour les indices (ex: -20% du score de base par indice)
        $malusIndices = ($scoreDeBase * 0.2) * $indicesUtilises;

        $scoreTotal = max(100, (int)($scoreDeBase - $malusTemps - $malusIndices));

        if ($type === 'text') {
            return (int)($scoreTotal * 0.4); // 40% pour le texte
        }

        if ($type === 'gps') {
            return (int)($scoreTotal * 0.6); // 60% pour le GPS
        }

        return $scoreTotal;
    }

    /**
     * Calcule le bonus pour les énigmes supplémentaires.
     */
    public function calculerBonus(Enigme $enigme): int
    {
        return $enigme->niveau * 500;
    }
}
