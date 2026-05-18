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
        Schema::create('indice_debloques', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();
                  
            $table->foreignId('session_jeu_id')
                  ->constrained('sessions_jeu')
                  ->cascadeOnDelete();
                  
            $table->foreignId('indice_id')
                  ->constrained('indices')
                  ->cascadeOnDelete();
                  
            $table->timestamps();
            
            // Un joueur ne débloque un indice qu'une seule fois par session
            $table->unique(['user_id', 'session_jeu_id', 'indice_id'], 'user_session_indice_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indice_debloques');
    }
};
