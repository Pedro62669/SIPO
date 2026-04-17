<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regras_fonte', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orcamento_id')->constrained('orcamentos')->cascadeOnDelete();
            $table->string('fonte_origem', 10);
            $table->string('fonte_destino', 10);
            $table->timestamps();

            $table->unique(['orcamento_id', 'fonte_origem']);
        });

        Schema::create('fonte_unidade_restricoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orcamento_id')->constrained('orcamentos')->cascadeOnDelete();
            $table->string('fonte_recurso_inicio', 10);
            $table->string('fonte_recurso_fim', 10);
            $table->foreignId('unidade_id')->constrained('unidades')->cascadeOnDelete();
            $table->foreignId('subunidade_id')->nullable()->constrained('subunidades')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('parametrizacoes_secretaria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orcamento_id')->constrained('orcamentos')->cascadeOnDelete();
            $table->foreignId('unidade_id')->constrained('unidades')->cascadeOnDelete();
            $table->foreignId('subunidade_id')->nullable()->constrained('subunidades')->nullOnDelete();
            $table->foreignId('fonte_id')->constrained('fontes_recurso')->cascadeOnDelete();
            $table->enum('classificacao', ['geral', 'custeio', 'pessoal', 'investimento', 'terceirizacao']);
            $table->decimal('percentual_anterior', 8, 2)->nullable();
            $table->bigInteger('valor_liberado')->default(0);
            $table->timestamps();
        });

        Schema::create('regras_percentual', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orcamento_id')->constrained('orcamentos')->cascadeOnDelete();
            $table->string('descricao');
            $table->foreignId('fonte_id')->nullable()->constrained('fontes_recurso')->nullOnDelete();
            $table->foreignId('unidade_id')->nullable()->constrained('unidades')->nullOnDelete();
            $table->decimal('percentual', 8, 2);
            $table->enum('tipo', ['obrigatorio', 'referencia'])->default('referencia');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regras_percentual');
        Schema::dropIfExists('parametrizacoes_secretaria');
        Schema::dropIfExists('fonte_unidade_restricoes');
        Schema::dropIfExists('regras_fonte');
    }
};
