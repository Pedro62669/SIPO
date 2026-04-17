<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE parametrizacoes_secretaria MODIFY COLUMN classificacao ENUM('geral','custeio','pessoal','investimento','terceirizacao') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE parametrizacoes_secretaria MODIFY COLUMN classificacao ENUM('custeio','pessoal','investimento','terceirizacao') NOT NULL");
    }
};
