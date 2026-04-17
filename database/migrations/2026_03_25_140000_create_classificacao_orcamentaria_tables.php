<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('funcoes', function (Blueprint $table) {
            $table->id();
            $table->integer('codigo')->unique();
            $table->string('descricao');
            $table->timestamps();
        });

        Schema::create('subfuncoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funcao_id')->constrained('funcoes')->cascadeOnDelete();
            $table->integer('codigo')->unique();
            $table->string('descricao');
            $table->timestamps();
        });

        Schema::create('programas', function (Blueprint $table) {
            $table->id();
            $table->integer('codigo')->unique();
            $table->string('descricao');
            $table->timestamps();
        });

        Schema::create('naturezas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20);
            $table->string('codigo_compacto', 10)->nullable();
            $table->string('descricao');
            $table->string('classificacao', 50)->nullable();
            $table->string('grupo', 4)->nullable();
            $table->timestamps();

            $table->unique('codigo');
        });

        Schema::create('fontes_recurso', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 10);
            $table->string('descricao');
            $table->string('recurso_vinculado')->nullable();
            $table->timestamps();

            $table->unique('codigo');
        });

        Schema::create('acoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programa_id')->constrained('programas')->cascadeOnDelete();
            $table->integer('codigo');
            $table->string('descricao');
            $table->enum('tipo_acao', ['0', '1', '2'])->nullable()->comment('0=Op.Especiais, 1=Obras, 2=Atividade');
            $table->timestamps();

            $table->unique(['programa_id', 'codigo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acoes');
        Schema::dropIfExists('fontes_recurso');
        Schema::dropIfExists('naturezas');
        Schema::dropIfExists('programas');
        Schema::dropIfExists('subfuncoes');
        Schema::dropIfExists('funcoes');
    }
};
