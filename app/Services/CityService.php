<?php

namespace App\Services;

use App\Models\Ville;
use App\Services\GPSService;

class CityService
{
    protected $gpsService;

    public function __construct(GPSService $gpsService)
    {
        $this->gpsService = $gpsService;
    }

    /**
     * Trouve la ville la plus proche des coordonnées données dans un rayon d'action.
     */
    public function trouverVilleProche(float $latitude, float $longitude): ?Ville
    {
        $villes = Ville::where('actif', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        $villeLaPlusProche = null;
        $distanceMin = PHP_FLOAT_MAX;

        foreach ($villes as $ville) {
            $distance = $this->gpsService->calculerDistance(
                $latitude,
                $longitude,
                (float) $ville->latitude,
                (float) $ville->longitude
            );

            // Convertir distance (mètres) en kilomètres pour comparer avec rayon_action
            $distanceKm = $distance / 1000;

            if ($distanceKm <= $ville->rayon_action) {
                if ($distanceKm < $distanceMin) {
                    $distanceMin = $distanceKm;
                    $villeLaPlusProche = $ville;
                }
            }
        }

        return $villeLaPlusProche;
    }
}
