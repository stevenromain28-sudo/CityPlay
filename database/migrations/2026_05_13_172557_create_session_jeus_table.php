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
        Schema::create('sessions_jeu', function (Blueprint $table) {

            $table->id();

            $table->foreignId('ville_id')
                ->constrained('villes')
                ->cascadeOnDelete();

            $table->foreignId('proprietaire_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->enum('mode', [
                'cooperatif',
                'mercenaire'
            ]);

            $table->enum('statut', [
                'en_attente',
                'actif',
                'pause',
                'termine'
            ])->default('en_attente');

            $table->integer('score')->default(0);

            $table->integer('progression')->default(0);

            $table->timestamp('commence_le')->nullable();

            $table->timestamp('termine_le')->nullable();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_jeus');
    }
};
