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
        Schema::table('sessions_jeu', function (Blueprint $table) {
            $table->enum('moyen_transport', ['pied', 'moto', 'voiture'])->default('pied')->after('mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sessions_jeu', function (Blueprint $table) {
            $table->dropColumn('moyen_transport');
        });
    }
};
