<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('despesas_importadas')) {
            return;
        }

        Schema::table('despesas_importadas', function (Blueprint $table) {
            if (! Schema::hasColumn('despesas_importadas', 'credito_suplementar')) {
                $table->bigInteger('credito_suplementar')->default(0)->after('liquidado');
            }
            if (! Schema::hasColumn('despesas_importadas', 'credito_especial')) {
                $table->bigInteger('credito_especial')->default(0)->after('credito_suplementar');
            }
            if (! Schema::hasColumn('despesas_importadas', 'reducao_creditos')) {
                $table->bigInteger('reducao_creditos')->default(0)->after('credito_especial');
            }
            if (! Schema::hasColumn('despesas_importadas', 'dotacao_atualizada')) {
                $table->bigInteger('dotacao_atualizada')->default(0)->after('reducao_creditos');
            }
            if (! Schema::hasColumn('despesas_importadas', 'pago')) {
                $table->bigInteger('pago')->default(0)->after('dotacao_atualizada');
            }
            if (! Schema::hasColumn('despesas_importadas', 'saldo_a_liquidar')) {
                $table->bigInteger('saldo_a_liquidar')->default(0)->after('pago');
            }
            if (! Schema::hasColumn('despesas_importadas', 'saldo_a_pagar')) {
                $table->bigInteger('saldo_a_pagar')->default(0)->after('saldo_a_liquidar');
            }
            if (! Schema::hasColumn('despesas_importadas', 'saldo_dotacao')) {
                $table->bigInteger('saldo_dotacao')->default(0)->after('saldo_a_pagar');
            }
            if (! Schema::hasColumn('despesas_importadas', 'saldo_disponivel')) {
                $table->bigInteger('saldo_disponivel')->default(0)->after('saldo_dotacao');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('despesas_importadas')) {
            return;
        }

        $columns = array_filter([
            'credito_suplementar',
            'credito_especial',
            'reducao_creditos',
            'dotacao_atualizada',
            'pago',
            'saldo_a_liquidar',
            'saldo_a_pagar',
            'saldo_dotacao',
            'saldo_disponivel',
        ], fn (string $column) => Schema::hasColumn('despesas_importadas', $column));

        if ($columns === []) {
            return;
        }

        Schema::table('despesas_importadas', function (Blueprint $table) use ($columns) {
            $table->dropColumn($columns);
        });
    }
};
