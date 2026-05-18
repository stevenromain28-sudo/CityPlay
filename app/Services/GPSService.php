<?php

namespace App\Services;

class GPSService
{
    /**
     * Calcule la distance entre deux points GPS en mètres utilisant la formule de Haversine.
     */
    public function calculerDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // Rayon de la Terre en mètres

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Valide si le joueur est dans le rayon de validation de l'énigme.
     */
    public function validerPosition(float $playerLat, float $playerLon, float $targetLat, float $targetLon, float $radius): bool
    {
        $distance = $this->calculerDistance($playerLat, $playerLon, $targetLat, $targetLon);
        
        return $distance <= $radius;
    }
}
