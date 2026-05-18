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
        Schema::create('progression_enigmes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_jeu_id')->constrained('sessions_jeu')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('enigme_id')->constrained('enigmes')->cascadeOnDelete();
            $table->timestamp('text_validated_at')->nullable();
            $table->timestamp('gps_validated_at')->nullable();
            $table->boolean('bonus_choice_made')->default(false);
            $table->boolean('wants_bonus')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progression_enigmes');
    }
};
