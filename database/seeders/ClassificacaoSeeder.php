<?php

namespace Database\Seeders;

use App\Models\Acao;
use App\Models\FonteRecurso;
use App\Models\Natureza;
use App\Models\Programa;
use Illuminate\Database\Seeder;

class ClassificacaoSeeder extends Seeder
{
    public function run(): void
    {
        $naturezas = json_decode(file_get_contents(database_path('seeders/data/naturezas.json')), true);
        foreach ($naturezas as $n) {
            Natureza::firstOrCreate(
                ['codigo' => $n['codigo']],
                [
                    'codigo_compacto' => $n['codigo_compacto'],
                    'descricao' => $n['descricao'],
                    'classificacao' => $n['classificacao'],
                    'grupo' => $n['grupo'],
                ]
            );
        }

        $fontes = json_decode(file_get_contents(database_path('seeders/data/fontes.json')), true);
        foreach ($fontes as $f) {
            FonteRecurso::firstOrCreate(
                ['codigo' => $f['codigo']],
                [
                    'descricao' => $f['descricao'],
                    'recurso_vinculado' => $f['recurso_vinculado'],
                ]
            );
        }

        $programas = json_decode(file_get_contents(database_path('seeders/data/programas.json')), true);
        foreach ($programas as $p) {
            Programa::firstOrCreate(
                ['codigo' => (int) $p['codigo']],
                ['descricao' => $p['descricao']]
            );
        }

        $acoes = json_decode(file_get_contents(database_path('seeders/data/acoes.json')), true);
        foreach ($acoes as $a) {
            $programa = Programa::where('codigo', (int) $a['programa_codigo'])->first();
            if ($programa) {
                Acao::firstOrCreate(
                    ['programa_id' => $programa->id, 'codigo' => (int) $a['codigo']],
                    ['descricao' => $a['descricao']]
                );
            }
        }
    }
}
