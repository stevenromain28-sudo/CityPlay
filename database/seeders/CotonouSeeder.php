<?php

namespace Database\Seeders;

use App\Models\Ville;
use App\Models\Lieu;
use App\Models\Enigme;
use App\Models\Indice;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CotonouSeeder extends Seeder
{
    public function run(): void
    {
        // On récupère ou crée un admin pour la ville
        $admin = User::where('email', 'admin@cityplay.fr')->first();

        // Ville : Cotonou
        $cotonou = Ville::updateOrCreate(
            ['slug' => 'cotonou'],
            [
                'user_id' => $admin ? $admin->id : null,
                'nom' => 'Cotonou',
                'description' => 'La vibrante capitale économique du Bénin, entre océan et lagune. Découvrez ses monuments historiques et ses marchés colorés.',
                'pays' => 'Bénin',
                'banniere' => 'https://images.unsplash.com/photo-1596464716127-f2a82984de30?w=1200&q=80',
                'actif' => true,
                'latitude' => 6.36536, // Centre de Cotonou
                'longitude' => 2.41833,
                'rayon_action' => 100, // 100km pour être large
            ]
        );

        // Lieu 1 : La Place de l'Amazone
        $placeAmazone = Lieu::updateOrCreate(
            ['nom' => "Place de l'Amazone", 'ville_id' => $cotonou->id],
            [
                'description' => "Un monument majestueux dédié aux femmes guerrières du Dahomey, symbole de courage et de fierté nationale.",
                'latitude' => 6.3496,
                'longitude' => 2.4345,
                'rayon' => 50,
                'image_principale' => 'https://images.unsplash.com/photo-1628155930542-3c7a64e2c833?w=800&q=80',
                'difficulte' => 1,
                'duree_estimee' => 20,
            ]
        );

        // Lieu 2 : La Place des Martyrs
        $placeMartyrs = Lieu::updateOrCreate(
            ['nom' => "Place des Martyrs", 'ville_id' => $cotonou->id],
            [
                'description' => "Un lieu historique commémorant les héros de la nation. Anciennement appelée Place du Souvenir.",
                'latitude' => 6.3621,
                'longitude' => 2.4178,
                'rayon' => 50,
                'image_principale' => 'https://images.unsplash.com/photo-1544013587-414f8fcf79c0?w=800&q=80',
                'difficulte' => 2,
                'duree_estimee' => 30,
            ]
        );

        // Lieu 3 : Le Marché Dantokpa
        $marcheDantokpa = Lieu::updateOrCreate(
            ['nom' => "Marché Dantokpa", 'ville_id' => $cotonou->id],
            [
                'description' => "Le plus grand marché à ciel ouvert d'Afrique de l'Ouest. Une véritable ville dans la ville.",
                'latitude' => 6.3725,
                'longitude' => 2.4312,
                'rayon' => 100,
                'image_principale' => 'https://images.unsplash.com/photo-1540910419316-ce017300c140?w=800&q=80',
                'difficulte' => 3,
                'duree_estimee' => 60,
            ]
        );

        // Enigme pour Dantokpa
        Enigme::create([
            'lieu_id' => $marcheDantokpa->id,
            'titre' => "Le labyrinthe des couleurs",
            'contenu' => "Trouvez l'entrée principale du grand bâtiment. Quelle est la couleur dominante des arches au-dessus des portes ?",
            'niveau' => 3,
            'ordre' => 3,
            'reponse' => 'Bleu',
            'latitude' => 6.3725,
            'longitude' => 2.4312,
            'rayon' => 20,
        ]);
    }
}
