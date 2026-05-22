<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ville;
use App\Models\Lieu;

class LieuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find the city Cotonou (created by VilleSeeder)
        $ville = Ville::where('nom', 'Cotonou')->first();
        if (! $ville) {
            // If not found, abort silently – the seeder can be re‑run after VilleSeeder.
            return;
        }

        // --- Lieu Ganhi ---
        Lieu::create([
            'ville_id' => $ville->id,
            'nom' => 'Marché de Ganhi',
            'description' => `Le marché de Ganhi, c’est le cœur commercial branché et historique du centre-ville de Cotonou. Contrairement au gigantisme à ciel ouvert de Dantokpa, Ganhi est un marché plus central, plus structuré et résolument urbain, situé dans le quartier des affaires.`,
            'localisation' => 'Quartier central',
            'latitude' => 6.35476210,
            'longitude' => 2.43810450,
            'rayon' => 100,
            'image_principale' => null,
            'difficulte' => 'hard',
            'duree_estimee' => 30,
        ]);

        // --- Lieu Amazone ---
        Lieu::create([
            'ville_id' => $ville->id,
            'nom' => `Place de L'Amazone`,
            'description' => `La statue de l’Amazone, c'est le nouveau symbole fort de la fierté, du courage et de la souveraineté du Bénin. Érigée au cœur de Cotonou sur l'esplanade qui porte son nom (juste derrière le palais de la Marina), cette œuvre monumentale rend hommage aux anciennes guerrières du royaume de Dahomey, les Agoodjié.`,
            'localisation' => `Place de L'Amazone`,
            'latitude' => 6.348952172525617,
            'longitude' => 2.4075283606548497,
            'rayon' => 100,
            'image_principale' => null,
            'difficulte' => 'medium',
            'duree_estimee' => 20,
        ]);
    }
}
?>
