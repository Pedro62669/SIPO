<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('despesas_importadas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orcamento_id')->constrained('orcamentos')->cascadeOnDelete();
            $table->integer('ano');
            $table->integer('numero_despesa')->nullable();
            $table->foreignId('natureza_id')->constrained('naturezas')->cascadeOnDelete();
            $table->foreignId('fonte_id')->constrained('fontes_recurso')->cascadeOnDelete();
            $table->foreignId('unidade_id')->constrained('unidades')->cascadeOnDelete();
            $table->foreignId('subunidade_id')->constrained('subunidades')->cascadeOnDelete();
            $table->foreignId('funcao_id')->nullable()->constrained('funcoes')->nullOnDelete();
            $table->foreignId('subfuncao_id')->nullable()->constrained('subfuncoes')->nullOnDelete();
            $table->foreignId('programa_id')->constrained('programas')->cascadeOnDelete();
            $table->foreignId('acao_id')->constrained('acoes')->cascadeOnDelete();
            $table->bigInteger('valor_inicial')->default(0);
            $table->bigInteger('empenhado')->default(0);
            $table->bigInteger('liquidado')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('despesas_importadas');
    }
};
