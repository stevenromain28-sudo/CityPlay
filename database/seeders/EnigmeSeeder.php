<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Lieu;
use App\Models\Enigme;

class EnigmeSeeder extends Seeder
{
    public function run(): void
    {
        // Retrieve the two lieux created earlier
        $ganhi = Lieu::where('nom', 'Marché de Ganhi')->firstOrFail();
        $amazone = Lieu::where('nom', "Place de L'Amazone")->firstOrFail();

        // ----- GANHI ENIGMES (3) -----
        Enigme::create([
            'lieu_id' => $ganhi->id,
            'titre' => 'Marché de Ganhi',
            'contenu' => "Je suis un grand marché public situé en plein cœur du centre-ville d'une grande métropole africaine. Chez moi, tu ne trouveras pas de montagnes de nourriture ou d'animaux, mais des kilomètres de tissus colorés, de fils et de boutons. C'est l'endroit où tout le monde se rend pour choisir le plus beau pagne avant de l'apporter chez le tailleur. On m'appelle le marché de... ?",
            'image' => null,
            'audio' => null,
            'niveau' => 1,
            'ordre' => 1,
            'reponse' => 'Marché,de,Ganhi',
            'latitude' => $ganhi->latitude,
            'longitude' => $ganhi->longitude,
            'rayon' => 200,
            'verification_gps' => false,
            'is_bonus' => false,
        ]);

        Enigme::create([
            'lieu_id' => $ganhi->id,
            'titre' => 'Marché de Ganhi',
            'contenu' => "Si tu cherches le cœur financier de la ville, tu marcheras au milieu des banques et des grands immeubles de verre. Mais juste là, caché au milieu de ce quartier très sérieux, je t'offre une explosion de couleurs.

Je ne suis pas le marché géant qui borde le lac au nord, je préfère le centre-ville. On vient chez moi pour acheter de quoi s'habiller et créer les plus beaux vêtements du pays. Mon nom commence par la 7ème lettre de l'alphabet.

Qui suis-je ?",
            'image' => null,
            'audio' => null,
            'niveau' => 2,
            'ordre' => 2,
            'reponse' => 'Marché,de,Ganhi',
            'latitude' => $ganhi->latitude,
            'longitude' => $ganhi->longitude,
            'rayon' => 200,
            'verification_gps' => false,
            'is_bonus' => false,
        ]);

        Enigme::create([
            'lieu_id' => $ganhi->id,
            'titre' => 'Marché de Ganhi',
            'contenu' => "Je me trouve dans la partie Sud de la ville, là où les affaires sont sérieuses mais où les vêtements sont joyeux.

Pour me trouver, tourne le dos à l'Océan Atlantique et marche vers le Nord, mais ne traverse pas la lagune : je m'arrête juste avant. Je suis un labyrinthe à taille humaine, coincé entre les grat-ciels et la nostalgie de l'ancien Cotonou. Si tu as besoin d'un fil, d'une aiguille ou d'une étoffe pour te fondre parmi les habitants, c'est mon nom que tu devras prononcer.

Qui suis-je ?",
            'image' => null,
            'audio' => null,
            'niveau' => 3,
            'ordre' => 3,
            'reponse' => 'Marché,de,Ganhi',
            'latitude' => $ganhi->latitude,
            'longitude' => $ganhi->longitude,
            'rayon' => 200,
            'verification_gps' => false,
            'is_bonus' => false,
        ]);

// ----- GANHI ENIGMES bonus (2) -----
         Enigme::create([
            'lieu_id' => $ganhi->id,
            'titre' => 'Le Trésor des Reines',
            'contenu' => "Félicitations, tu as trouvé Ganhi ! Mais le voyage ne s'arrête pas là. Tourne ton regard vers l'intérieur du marché, là où la lumière joue avec les tissus.

Tu cherches le véritable trésor du lieu : un tissu en coton très célèbre, coloré des deux côtés, dont le nom commence par un W et se termine par un X. Trouve une vendeuse, demande-lui de te montrer son plus beau modèle et prends-le en photo pour valider cette étape !",
            'image' => null,
            'audio' => null,
            'niveau' => 1,
            'ordre' => 3,
            'reponse' => 'Le tissu Wax,Pagne Wax',
            'latitude' => $ganhi->latitude,
            'longitude' => $ganhi->longitude,
            'rayon' => 200,
            'verification_gps' => false,
            'is_bonus' => true,
        ]);
         Enigme::create([
            'lieu_id' => $ganhi->id,
            'titre' => 'Marché de Ganhi',
            'contenu' => "Pour cette dernière étape, place-toi au centre du marché, là où les tissus s'entassent. Maintenant, lève les yeux vers le ciel, au-dessus des toits du marché.

Tu apercevras un immense bâtiment moderne en verre qui domine tout le quartier. C'est le cœur de la plus ancienne banque commerciale du pays. Trouve le nom de cette banque écrit en grand sur l'immeuble pour terminer ton exploration de Ganhi !",
            'image' => null,
            'audio' => null,
            'niveau' => 3,
            'ordre' => 3,
            'reponse' => "Société Générale,Ecobank",
            'latitude' => $ganhi->latitude,
            'longitude' => $ganhi->longitude,
            'rayon' => 200,
            'verification_gps' => false,
            'is_bonus' => true,
        ]);
        // ----- AMAZONE ENIGMES (3) -----
        Enigme::create([
            'lieu_id' => $amazone->id,
            'titre' => "Place de L'Amazone",
            'contenu' => "Je suis la plus grande et la plus célèbre statue de tout le Bénin ! Du haut de mes 30 mètres de bronze, je représente une femme guerrière très forte et fière, une lance à la main.

Mon esplanade se trouve en bord de mer à Cotonou, juste à côté du Palais de la Présidence. Tous les touristes et les habitants viennent me prendre en photo en fin de journée.

Qui suis-je ?",
            'image' => null,
            'audio' => null,
            'niveau' => 1,
            'ordre' => 1,
            'reponse' => "Place de L'Amazone,la Statue de l'Amazone",
            'latitude' => $amazone->latitude,
            'longitude' => $amazone->longitude,
            'rayon' => 200,
            'verification_gps' => false,
            'is_bonus' => false,
        ]);

        Enigme::create([
            'lieu_id' => $amazone->id,
            'titre' => "Place de L'Amazone",
            'contenu' => "Je suis une immense figure de bronze qui garde la ville depuis mes 30 mètres de hauteur. Tu me trouveras sur une grande esplanade, juste à côté du boulevard de la Marina et non loin du palais présidentiel.

Je ne représente pas un roi ou un président, mais la force, la fierté et le courage des femmes de ce pays. Dans mes mains, je porte des armes pour défendre ma patrie.

Qui suis-je ?",
            'image' => null,
            'audio' => null,
            'niveau' => 2,
            'ordre' => 2,
            'reponse' => "Place de L'Amazone,la Statue de l'Amazone",
            'latitude' => $amazone->latitude,
            'longitude' => $amazone->longitude,
            'rayon' => 200,
            'verification_gps' => false,
            'is_bonus' => false,
        ]);

        Enigme::create([
            'lieu_id' => $amazone->id,
            'titre' => "Place de L'Amazone",
            'contenu' => "Tourne ton regard vers le ciel de la côte, non loin de l’océan. Je suis une géante de 150 tonnes qui ne dort jamais, les pieds ancrés dans le sol de Cotonou et les yeux fixés vers l'horizon.

Si tu longes les grands murs peints de graffitis du centre-ville, tu finiras par arriver sur mon esplanade. Je suis le symbole d'une armée féminine unique au monde qui a marqué l'histoire du pays, et aujourd'hui, tout le monde vient se promener à mes pieds pour ressentir ma puissance.

Qui suis-je ?",
            'image' => null,
            'audio' => null,
            'niveau' => 3,
            'ordre' => 3,
            'reponse' => "Place de L'Amazone,la Statue de l'Amazone",
            'latitude' => $amazone->latitude,
            'longitude' => $amazone->longitude,
            'rayon' => 200,
            'verification_gps' => false,
            'is_bonus' => false,
        ]);

        // ----- AMAZONE ENIGMES bonus (1) -----
            Enigme::create([
            'lieu_id' => $amazone->id,
            'titre' => "Place de L'Amazone",
            'contenu' => "Tu es enfin face à la géante de bronze ! Regarde-la bien. Pour valider ton arrivée, tu dois inspecter son équipement de guerrière.

Dans sa main gauche, elle tient un fusil, mais dans sa main droite, elle brandit une arme blanche traditionnelle, symbole de sa puissance au combat. Quel est cet objet pointu qui s'élève vers le ciel ? Regarde bien à ses pieds pour trouver la réponse !",
            'image' => null,
            'audio' => null,
            'niveau' => 3,
            'ordre' => 3,
            'reponse' => "Une lance",
            'latitude' => $amazone->latitude,
            'longitude' => $amazone->longitude,
            'rayon' => 200,
            'verification_gps' => false,
            'is_bonus' => true,
        ]);

    }
}
?>
