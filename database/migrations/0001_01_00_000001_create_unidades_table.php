<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unidades', function (Blueprint $table) {
            $table->id();
            $table->integer('codigo')->unique();
            $table->string('descricao');
            $table->timestamps();
        });

        Schema::create('subunidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unidade_id')->constrained('unidades')->cascadeOnDelete();
            $table->integer('codigo');
            $table->string('descricao');
            $table->timestamps();

            $table->unique(['unidade_id', 'codigo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subunidades');
        Schema::dropIfExists('unidades');
    }
};
