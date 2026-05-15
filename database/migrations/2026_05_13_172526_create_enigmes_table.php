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
        Schema::create('enigmes', function (Blueprint $table) {

            $table->id();

            $table->foreignId('lieu_id')
                ->constrained('lieux')
                ->cascadeOnDelete();

            $table->string('titre');

            $table->longText('contenu');

            $table->string('image')->nullable();

            $table->integer('niveau')->default(1);

            $table->integer('ordre')->default(1);

            $table->text('reponse')->nullable();

            $table->decimal('latitude', 10, 7)->nullable();

            $table->decimal('longitude', 10, 7)->nullable();

            $table->integer('rayon')->default(50);

            $table->boolean('verification_gps')->default(true);

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enigmes');
    }
};
