<?php

namespace Database\Seeders;

use App\Models\Subunidade;
use App\Models\Unidade;
use Illuminate\Database\Seeder;

class UnidadeSeeder extends Seeder
{
    public function run(): void
    {
        $unidades = json_decode(file_get_contents(database_path('seeders/data/unidades.json')), true);
        $subunidades = json_decode(file_get_contents(database_path('seeders/data/subunidades.json')), true);

        foreach ($unidades as $u) {
            Unidade::firstOrCreate(
                ['codigo' => (int) $u['codigo']],
                ['descricao' => $u['descricao']]
            );
        }

        foreach ($subunidades as $s) {
            $unidade = Unidade::where('codigo', (int) $s['unidade_codigo'])->first();
            if ($unidade) {
                Subunidade::firstOrCreate(
                    ['unidade_id' => $unidade->id, 'codigo' => (int) $s['codigo']],
                    ['descricao' => $s['descricao']]
                );
            }
        }
    }
}
