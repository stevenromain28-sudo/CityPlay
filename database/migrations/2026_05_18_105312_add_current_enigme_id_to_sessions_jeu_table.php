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
            $table->foreignId('current_enigme_id')->nullable()->constrained('enigmes')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sessions_jeu', function (Blueprint $table) {
            $table->dropConstrainedForeignId('current_enigme_id');
        });
    }
};
