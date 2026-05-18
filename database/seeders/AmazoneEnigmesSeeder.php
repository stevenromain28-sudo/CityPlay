<?php

namespace Database\Seeders;

use App\Models\Lieu;
use App\Models\Enigme;
use App\Models\Indice;
use Illuminate\Database\Seeder;

class AmazoneEnigmesSeeder extends Seeder
{
    public function run(): void
    {
        // On récupère le lieu de l'Amazone
        $placeAmazone = Lieu::where('nom', "Place de l'Amazone")->first();

        if (!$placeAmazone) {
            return;
        }

        // Nettoyer les énigmes existantes pour ce lieu
        $enigmeIds = Enigme::where('lieu_id', $placeAmazone->id)->pluck('id');
        Indice::whereIn('enigme_id', $enigmeIds)->delete();
        Enigme::where('lieu_id', $placeAmazone->id)->delete();

        // Énigme Niveau 1
        $e1 = Enigme::create([
            'lieu_id' => $placeAmazone->id,
            'titre' => "Le regard d'acier",
            'contenu' => "L'Amazone regarde vers l'horizon avec détermination. Que tient-elle fermement dans sa main gauche ?",
            'image' => 'https://images.unsplash.com/photo-1628155930542-3c7a64e2c833?w=800&q=80',
            'niveau' => 1,
            'ordre' => 1,
            'reponse' => 'Fusil',
            'latitude' => 6.3496,
            'longitude' => 2.4345,
            'rayon' => 15,
        ]);
        Indice::create([
            'enigme_id' => $e1->id,
            'contenu' => "C'est une arme de défense emblématique des guerrières.",
            'penalite' => 10
        ]);

        // Énigme Niveau 2
        $e2 = Enigme::create([
            'lieu_id' => $placeAmazone->id,
            'titre' => "Le socle des braves",
            'contenu' => "Approchez-vous du socle majestueux. Sur la face arrière, une inscription rend hommage à une année spécifique. Quelle est cette année ?",
            'niveau' => 2,
            'ordre' => 2,
            'reponse' => '2022',
            'latitude' => 6.3496,
            'longitude' => 2.4345,
            'rayon' => 10,
        ]);
        Indice::create([
            'enigme_id' => $e2->id,
            'contenu' => "Regardez bien les gravures dans le marbre noir à l'opposé du boulevard.",
            'penalite' => 20
        ]);

        // Énigme Niveau 3
        $e3 = Enigme::create([
            'lieu_id' => $placeAmazone->id,
            'titre' => "La parure de la guerrière",
            'contenu' => "Observez les ornements sur le cou de la statue. Combien de rangées de colliers distinctes pouvez-vous compter ?",
            'niveau' => 3,
            'ordre' => 3,
            'reponse' => '4',
            'latitude' => 6.3496,
            'longitude' => 2.4345,
            'rayon' => 5,
        ]);
        Indice::create([
            'enigme_id' => $e3->id,
            'contenu' => "Il faut être très proche pour ne pas confondre les plis du métal avec les bijoux.",
            'penalite' => 30
        ]);
    }
}
