<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Ensure roles exist
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'player']);

        // Create Default SuperAdmin User
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@cityplay.fr'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
            ]
        );

        $superAdmin->assignRole($superAdminRole);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $superAdmin = User::where('email', 'superadmin@cityplay.fr')->first();
        if ($superAdmin) {
            $superAdmin->delete();
        }

        Role::where('name', 'super_admin')->delete();
    }
};
