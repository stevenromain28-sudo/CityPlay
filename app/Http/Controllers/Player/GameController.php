<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\Enigme;
use Illuminate\Http\Request;

class GameController extends Controller
{
    /**
     * Valide la position GPS du joueur par rapport à une énigme.
     */
    public function validateLocation(Request $request, Enigme $enigme)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if (!$enigme->verification_gps || !$enigme->latitude || !$enigme->longitude) {
            return response()->json([
                'success' => true,
                'message' => 'Validation GPS ignorée pour cette énigme.'
            ]);
        }

        $distance = $this->calculateDistance(
            $request->latitude,
            $request->longitude,
            $enigme->latitude,
            $enigme->longitude
        );

        $rayon = $enigme->rayon ?: 50; // 50m par défaut

        if ($distance <= $rayon) {
            return response()->json([
                'success' => true,
                'distance' => round($distance, 2),
                'message' => 'Félicitations ! Vous êtes au bon endroit.'
            ]);
        }

        return response()->json([
            'success' => false,
            'distance' => round($distance, 2),
            'message' => 'Vous n\'êtes pas encore assez proche du secret...'
        ], 422);
    }

    /**
     * Formule de Haversine pour calculer la distance entre deux points GPS en mètres.
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
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
}
