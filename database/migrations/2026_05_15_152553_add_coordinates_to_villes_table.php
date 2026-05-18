<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('villes', function (Blueprint $table) {
            if (!Schema::hasColumn('villes', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable()->after('pays');
            }
            if (!Schema::hasColumn('villes', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            }
            if (!Schema::hasColumn('villes', 'rayon_action')) {
                $table->integer('rayon_action')->default(50)->after('longitude'); // Rayon en km
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('villes', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'rayon_action']);
        });
    }
};
