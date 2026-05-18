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
        Schema::table('enigmes', function (Blueprint $table) {
            if (!Schema::hasColumn('enigmes', 'audio')) {
                $table->string('audio')->nullable()->after('image');
            }
            if (!Schema::hasColumn('enigmes', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('reponse');
            }
            if (!Schema::hasColumn('enigmes', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
            if (!Schema::hasColumn('enigmes', 'rayon')) {
                $table->integer('rayon')->default(50)->after('longitude');
            }
            if (!Schema::hasColumn('enigmes', 'verification_gps')) {
                $table->boolean('verification_gps')->default(true)->after('rayon');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enigmes', function (Blueprint $table) {
            $table->dropColumn(['audio', 'latitude', 'longitude', 'rayon', 'verification_gps']);
        });
    }
};
