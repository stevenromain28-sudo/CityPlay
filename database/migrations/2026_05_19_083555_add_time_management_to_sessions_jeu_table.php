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
            $table->integer('duree_initiale')->default(45)->after('mode'); // En minutes
            $table->integer('temps_restant')->default(2700)->after('duree_initiale'); // En secondes (45 * 60)
            $table->timestamp('dernier_calcul_at')->nullable()->after('temps_restant');
            // Mise à jour de l'enum statut pour inclure 'temps_epuise'
            $table->enum('statut', ['en_attente', 'actif', 'pause', 'termine', 'temps_epuise'])->default('en_attente')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sessions_jeu', function (Blueprint $table) {
            $table->dropColumn(['duree_initiale', 'temps_restant', 'dernier_calcul_at']);
            $table->enum('statut', ['en_attente', 'actif', 'pause', 'termine'])->default('en_attente')->change();
        });
    }
};
