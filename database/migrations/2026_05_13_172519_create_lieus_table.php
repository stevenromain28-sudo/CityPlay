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
    Schema::create('lieux', function (Blueprint $table) {

    $table->id();
    $table->foreignId('ville_id')->constrained()->onDelete('cascade');
    $table->string('nom');
    $table->text('description')->nullable();
    $table->string('localisation')->nullable(); // Adresse ou nom du quartier
    $table->decimal('latitude', 10, 8);
    $table->decimal('longitude', 11, 8);
    $table->integer('rayon')->default(50);
    $table->string('image_principale')->nullable();
    $table->integer('difficulte')->default(1);
    $table->integer('duree_estimee')->nullable();
    $table->timestamps();

});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lieux');
    }
};
