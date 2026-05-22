<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Equipe;
use Spatie\Permission\Models\Role;

class TeamAndPlayersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // ----- Second admin (without team) -----
        $admin2 = User::factory()->create([
            'name' => 'Admin 2 CityPlay',
            'email' => 'admin2@cityplay.fr',
            'password' => bcrypt('password'),
        ]);
        $admin2->assignRole('admin');

        // Create a player who will be the chef of the team
        $chefAlpha = User::factory()->create([
            'name' => 'Chef Alpha',
            'email' => 'chefalpha@cityplay.fr',
            'password' => bcrypt('password'),
            'role_equipe' => 'chef',
        ]);
        $chefAlpha->assignRole('player');

        // Create the team with the chef player as chef_id
        $teamAlpha = Equipe::create([
            'chef_id' => $chefAlpha->id,
            'nom' => 'Equipe Alpha',
            'score_total' => 0,
        ]);

        // ----- Players belonging to the same team (Equipe Alpha) -----
        $playerA = User::factory()->create([
            'name' => 'Joueur Alpha 1',
            'email' => 'playeralpha1@cityplay.fr',
            'password' => bcrypt('password'),
            'equipe_id' => $teamAlpha->id,
            'role_equipe' => 'membre',
        ]);
        $playerA->assignRole('player');

        $playerB = User::factory()->create([
            'name' => 'Joueur Alpha 2',
            'email' => 'playeralpha2@cityplay.fr',
            'password' => bcrypt('password'),
            'equipe_id' => $teamAlpha->id,
            'role_equipe' => 'membre',
        ]);
        $playerB->assignRole('player');

        // ----- Second admin (without team) -----
        $admin3 = User::factory()->create([
            'name' => 'Admin 3 CityPlay',
            'email' => 'admin3@cityplay.fr',
            'password' => bcrypt('password'),
        ]);
        $admin3->assignRole('admin');

        // Create a player who will be the chef of team Beta
        $chefBeta = User::factory()->create([
            'name' => 'Chef Beta',
            'email' => 'chefbeta@cityplay.fr',
            'password' => bcrypt('password'),
            'role_equipe' => 'chef',
        ]);
        $chefBeta->assignRole('player');

        // Create the team with the chef player as chef_id
        $teamBeta = Equipe::create([
            'chef_id' => $chefBeta->id,
            'nom' => 'Equipe Beta',
            'score_total' => 0,
        ]);

        $playerBeta = User::factory()->create([
            'name' => 'Joueur Beta',
            'email' => 'playerbeta@cityplay.fr',
            'password' => bcrypt('password'),
            'equipe_id' => $teamBeta->id,
            'role_equipe' => 'membre',
        ]);
        $playerBeta->assignRole('player');
    }
}
?>
