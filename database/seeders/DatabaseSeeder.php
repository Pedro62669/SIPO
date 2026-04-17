<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UnidadeSeeder::class,
            ClassificacaoSeeder::class,
            RoleSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
}
