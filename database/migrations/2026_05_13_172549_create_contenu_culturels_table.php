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
        Schema::create('contenus_culturels', function (Blueprint $table) {

            $table->id();

            $table->foreignId('lieu_id')
                ->constrained('lieux')
                ->cascadeOnDelete();

            $table->string('titre');

            $table->longText('description');

            $table->string('audio')->nullable();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contenu_culturels');
    }
};
