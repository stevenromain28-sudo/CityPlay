<?php

namespace Database\Seeders;

use App\Models\Ville;
use App\Models\Lieu;
use App\Models\Enigme;
use App\Models\Indice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CityPlaySeeder extends Seeder
{
    public function run(): void
    {
        $admin = \App\Models\User::where('email', 'admin@cityplay.fr')->first();

        // Ville : Annecy
        $annecy = Ville::create([
            'user_id' => $admin ? $admin->id : null,
            'nom' => 'Annecy',
            'slug' => Str::slug('Annecy'),
            'description' => 'Surnommée la Venise des Alpes, Annecy est célèbre pour son lac et sa vieille ville pittoresque.',
            'pays' => 'France',
            'banniere' => '/images/backgrounds/img1.jpg',
            'actif' => true,
        ]);

        // Lieu : Le Palais de l'Île
        $palaisIle = Lieu::create([
            'ville_id' => $annecy->id,
            'nom' => "Le Palais de l'Île",
            'description' => "Monument emblématique d'Annecy, ce palais a servi de prison et de palais de justice.",
            'latitude' => 45.8992,
            'longitude' => 6.1264,
            'rayon' => 30,
            'image_principale' => '/images/backgrounds/img2.jpg',
            'difficulte' => 2,
            'duree_estimee' => 45,
        ]);

        // Enigme : L'ombre du prisonnier
        $enigme = Enigme::create([
            'lieu_id' => $palaisIle->id,
            'titre' => "L'ombre du prisonnier",
            'contenu' => "Cherchez la fenêtre aux barreaux tordus sur la façade nord. Quel animal est sculpté juste en dessous ?",
            'image' => '/images/backgrounds/img1.jpg',
            'niveau' => 2,
            'ordre' => 1,
            'reponse' => 'Lion',
            'latitude' => 45.8993,
            'longitude' => 6.1265,
            'rayon' => 10,
            'verification_gps' => true,
        ]);

        // Indice
        Indice::create([
            'enigme_id' => $enigme->id,
            'contenu' => "C'est le roi de la savane.",
            'penalite' => 10,
        ]);
    }
}
