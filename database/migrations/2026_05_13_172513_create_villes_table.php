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
      Schema::create('villes', function (Blueprint $table) {

    $table->id();

    $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');

    $table->string('nom');
    $table->string('slug')->unique();
    $table->text('description')->nullable();
    $table->text('history')->nullable();
    $table->string('pays')->nullable();
    $table->integer('population')->nullable();
    $table->decimal('latitude', 10, 8)->nullable();
    $table->decimal('longitude', 11, 8)->nullable();
    $table->string('banniere')->nullable();
    $table->boolean('actif')->default(true);
    $table->timestamps();

});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('villes');
    }
};
