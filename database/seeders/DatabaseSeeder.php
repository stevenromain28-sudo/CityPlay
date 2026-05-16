<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            CityPlaySeeder::class,
            CotonouSeeder::class,
            AmazoneEnigmesSeeder::class,
        ]);

        $admin = User::factory()->create([
            'name' => 'Admin CityPlay',
            'email' => 'admin@cityplay.fr',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        $player = User::factory()->create([
            'name' => 'Joueur 1',
            'email' => 'player@cityplay.fr',
            'password' => bcrypt('password'),
        ]);
        $player->assignRole('player');

        // Maintenant on peut appeler CityPlaySeeder car l'admin existe
        $this->call([
            CityPlaySeeder::class,
        ]);
    }
}
