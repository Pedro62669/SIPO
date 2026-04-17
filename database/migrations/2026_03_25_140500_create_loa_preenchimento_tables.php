<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loa_acoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orcamento_id')->constrained('orcamentos')->cascadeOnDelete();
            $table->foreignId('unidade_id')->constrained('unidades')->cascadeOnDelete();
            $table->foreignId('subunidade_id')->constrained('subunidades')->cascadeOnDelete();
            $table->foreignId('acao_original_id')->nullable()->constrained('acoes')->nullOnDelete();
            $table->enum('tipo_acao', ['0', '1', '2'])->nullable()->comment('0=Op.Especiais, 1=Obras, 2=Atividade');
            $table->string('nome');
            $table->enum('status', ['ativa', 'excluida', 'nova', 'editada'])->default('ativa');
            $table->string('nome_anterior')->nullable();
            $table->timestamps();
        });

        Schema::create('loa_preenchimentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orcamento_id')->constrained('orcamentos')->cascadeOnDelete();
            $table->foreignId('unidade_id')->constrained('unidades')->cascadeOnDelete();
            $table->foreignId('subunidade_id')->constrained('subunidades')->cascadeOnDelete();
            $table->foreignId('loa_acao_id')->constrained('loa_acoes')->cascadeOnDelete();
            $table->foreignId('natureza_id')->constrained('naturezas')->cascadeOnDelete();
            $table->foreignId('fonte_id')->constrained('fontes_recurso')->cascadeOnDelete();
            $table->string('detalhamento')->nullable();
            $table->bigInteger('valor')->default(0);
            $table->text('observacao')->nullable();
            $table->timestamps();
        });

        Schema::create('envios_orcamento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orcamento_id')->constrained('orcamentos')->cascadeOnDelete();
            $table->foreignId('unidade_id')->constrained('unidades')->cascadeOnDelete();
            $table->enum('status', ['rascunho', 'enviado'])->default('rascunho');
            $table->timestamp('enviado_em')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['orcamento_id', 'unidade_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('envios_orcamento');
        Schema::dropIfExists('loa_preenchimentos');
        Schema::dropIfExists('loa_acoes');
    }
};
