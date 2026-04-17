<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orcamento_id')->constrained('orcamentos')->cascadeOnDelete();
            $table->string('natureza_receita', 30);
            $table->string('descricao');
            $table->string('fonte_recurso', 10);
            $table->bigInteger('valor')->default(0);
            $table->boolean('eh_deducao')->default(false);
            $table->decimal('percentual_projecao', 8, 2)->nullable();
            $table->bigInteger('valor_projetado')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receitas');
    }
};
