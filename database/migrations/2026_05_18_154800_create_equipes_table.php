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
        Schema::create('equipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chef_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('nom')->nullable();
            $table->integer('score_total')->default(0);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('equipe_id')
                ->nullable()
                ->constrained('equipes')
                ->nullOnDelete()
                ->after('id');
            $table->enum('role_equipe', ['chef', 'membre', 'invite'])
                ->nullable()
                ->after('equipe_id');
        });

        Schema::table('joueur_sessions', function (Blueprint $table) {
            $table->foreignId('equipe_id')
                ->nullable()
                ->constrained('equipes')
                ->cascadeOnDelete()
                ->after('id');
        });

        Schema::table('progression_enigmes', function (Blueprint $table) {
            $table->foreignId('equipe_id')
                ->nullable()
                ->constrained('equipes')
                ->cascadeOnDelete()
                ->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('progression_enigmes', function (Blueprint $table) {
            $table->dropForeign(['equipe_id']);
            $table->dropColumn('equipe_id');
        });

        Schema::table('joueur_sessions', function (Blueprint $table) {
            $table->dropForeign(['equipe_id']);
            $table->dropColumn('equipe_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['equipe_id']);
            $table->dropColumn(['equipe_id', 'role_equipe']);
        });

        Schema::dropIfExists('equipes');
    }
};
