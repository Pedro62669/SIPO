<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('despesas_importadas', function (Blueprint $table) {
            $table->bigInteger('credito_suplementar')->default(0)->after('liquidado');
            $table->bigInteger('credito_especial')->default(0)->after('credito_suplementar');
            $table->bigInteger('reducao_creditos')->default(0)->after('credito_especial');
            $table->bigInteger('dotacao_atualizada')->default(0)->after('reducao_creditos');
            $table->bigInteger('pago')->default(0)->after('dotacao_atualizada');
            $table->bigInteger('saldo_a_liquidar')->default(0)->after('pago');
            $table->bigInteger('saldo_a_pagar')->default(0)->after('saldo_a_liquidar');
            $table->bigInteger('saldo_dotacao')->default(0)->after('saldo_a_pagar');
            $table->bigInteger('saldo_disponivel')->default(0)->after('saldo_dotacao');
        });
    }

    public function down(): void
    {
        Schema::table('despesas_importadas', function (Blueprint $table) {
            $table->dropColumn([
                'credito_suplementar',
                'credito_especial',
                'reducao_creditos',
                'dotacao_atualizada',
                'pago',
                'saldo_a_liquidar',
                'saldo_a_pagar',
                'saldo_dotacao',
                'saldo_disponivel',
            ]);
        });
    }
};
