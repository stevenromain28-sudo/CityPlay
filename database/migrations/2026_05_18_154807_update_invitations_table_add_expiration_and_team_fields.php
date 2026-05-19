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
        Schema::table('invitations', function (Blueprint $table) {
            if (!Schema::hasColumn('invitations', 'inviteur_id')) {
                $table->foreignId('inviteur_id')
                    ->constrained('users')
                    ->cascadeOnDelete()
                    ->after('id');
            }
            
            if (!Schema::hasColumn('invitations', 'equipe_id')) {
                $table->foreignId('equipe_id')
                    ->nullable()
                    ->constrained('equipes')
                    ->cascadeOnDelete()
                    ->after('session_jeu_id');
            }

            if (!Schema::hasColumn('invitations', 'expire_le')) {
                $table->timestamp('expire_le')->nullable()->after('token');
            }
            
            if (!Schema::hasColumn('invitations', 'max_utilisations')) {
                $table->unsignedInteger('max_utilisations')->default(1)->after('expire_le');
            }
            
            if (!Schema::hasColumn('invitations', 'utilisations')) {
                $table->unsignedInteger('utilisations')->default(0)->after('max_utilisations');
            }
            
            if (!Schema::hasColumn('invitations', 'type')) {
                $table->enum('type', ['solo_equipe', 'session'])->default('solo_equipe')->after('utilisations');
            }
            
            if (Schema::hasColumn('invitations', 'email')) {
                $table->dropColumn(['email', 'telephone']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            if (Schema::hasColumn('invitations', 'inviteur_id')) {
                $table->dropForeign(['inviteur_id']);
                $table->dropColumn('inviteur_id');
            }
            if (Schema::hasColumn('invitations', 'equipe_id')) {
                $table->dropForeign(['equipe_id']);
                $table->dropColumn('equipe_id');
            }
            $columnsToDrop = ['expire_le', 'max_utilisations', 'utilisations', 'type'];
            foreach ($columnsToDrop as $col) {
                if (Schema::hasColumn('invitations', $col)) {
                    $table->dropColumn($col);
                }
            }
            if (!Schema::hasColumn('invitations', 'email')) {
                $table->string('email')->nullable();
                $table->string('telephone')->nullable();
            }
        });
    }
};
