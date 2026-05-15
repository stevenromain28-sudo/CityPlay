<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('joueur_sessions', function (Blueprint $table) {

            $table->id();

            $table->foreignId('session_jeu_id')
                ->constrained('sessions_jeu')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('type', [
                'proprietaire',
                'partenaire',
                'mercenaire'
            ]);

            $table->integer('score')->default(0);

            $table->integer('progression')->default(0);

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('joueur_sessions');
    }
};
