<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('despesas_importadas') || Schema::hasColumn('despesas_importadas', 'total_creditos_adicionais')) {
            return;
        }

        Schema::table('despesas_importadas', function (Blueprint $table) {
            $table->bigInteger('total_creditos_adicionais')->default(0)->after('credito_especial');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('despesas_importadas') || ! Schema::hasColumn('despesas_importadas', 'total_creditos_adicionais')) {
            return;
        }

        Schema::table('despesas_importadas', function (Blueprint $table) {
            $table->dropColumn('total_creditos_adicionais');
        });
    }
};
