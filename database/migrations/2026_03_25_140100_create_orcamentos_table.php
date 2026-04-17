<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orcamentos', function (Blueprint $table) {
            $table->id();
            $table->integer('ano');
            $table->enum('tipo', ['LOA', 'PPA']);
            $table->enum('status', ['aberto', 'finalizado'])->default('aberto');
            $table->integer('periodo_ppa_inicio')->nullable();
            $table->integer('periodo_ppa_fim')->nullable();
            $table->date('prazo_preenchimento')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('orcamento_prazos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orcamento_id')->constrained('orcamentos')->cascadeOnDelete();
            $table->foreignId('unidade_id')->constrained('unidades')->cascadeOnDelete();
            $table->date('prazo_estendido');
            $table->timestamps();

            $table->unique(['orcamento_id', 'unidade_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orcamento_prazos');
        Schema::dropIfExists('orcamentos');
    }
};
