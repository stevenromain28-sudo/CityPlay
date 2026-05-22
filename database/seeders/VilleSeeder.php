<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Ville;

class VilleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find the first admin (created in DatabaseSeeder)
        $admin = User::where('email', 'admin@cityplay.fr')->first();
        if (! $admin) {
            // fallback: use any admin user
            $admin = User::role('admin')->first();
        }

        // Create the city Cotonou and assign it to the admin
        Ville::create([
            'user_id' => $admin->id,
            'nom' => 'Cotonou',
            'slug' => 'cotonou',
            'description' => `Cotonou est une ville en mouvement perpétuel. Dès le matin, les avenues s'animent au rythme des Zémidjans (les fameux taxis-motos en chemise jaune) qui se faufilent partout avec une agilité impressionnante. C'est une ville vibrante, bruyante, chaleureuse, où l'on ressent direct l'ambiance des grandes métropoles côtières africaines.`,
            'history' => `Au XIXe siècle, Cotonou n’était qu’un tout petit village de pêcheurs, coincé entre l'océan Atlantique et le lac Nokoué. Son nom d'origine, « Kútɔ́nû » en langue fon, signifie littéralement « l'embouchure du fleuve de la mort », en référence à la lagune.

                            À cette époque, la région est sous le contrôle du puissant royaume de Dahomey (basé à Abomey). Le roi Guézo, puis le roi Glèlè, y établissent un petit poste de commerce et de douane.

                            Le grand tournant historique a lieu en 1868 : les Français signent un traité avec le roi Glèlè pour s'installer à Cotonou. Mais quelques années plus tard, le roi Béhanzin, successeur de Glèlè, refuse cette présence coloniale. Cotonou devient alors le théâtre de violents combats en 1890 lors de la première guerre du Dahomey, notamment près de son ancien wharf (le ponton d'embarquement).

                            Une fois la région colonisée, les Français choisissent Cotonou pour y construire un grand port en eau profonde et le point de départ du chemin de fer. C'est ce port qui va tout changer : Porto-Novo reste la capitale politique officielle, mais Cotonou attire tous les commerçants, les travailleurs et l'énergie du pays.

                            En l'espace d'un siècle, le petit village de pêcheurs est devenu la capitale économique vibrante du Bénin, une métropole incontournable d'Afrique de l'Ouest.`,
            'pays' => 'Benin',
            'population' => 750000,
            'banniere' => null,
            'actif' => true,
            'latitude' => 6.36769530,
            'longitude' => 2.42525070,
            'rayon_action' => 5,
        ]);
    }
}
