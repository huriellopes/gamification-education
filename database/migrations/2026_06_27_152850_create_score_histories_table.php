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
        Schema::create('score_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('points'); // Pontos adicionados (positivo) ou deduzidos (negativo)
            $table->string('source_type'); // ex: 'test', 'material'
            $table->unsignedBigInteger('source_id'); // ID correspondente à origem
            $table->string('description'); // Descrição da pontuação, ex: "Leitura do material: Eloquent ORM"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('score_histories');
    }
};
