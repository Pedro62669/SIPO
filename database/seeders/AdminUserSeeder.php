<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@sipo.gov.br'],
            [
                'name' => 'Administrador SIPO',
                'password' => bcrypt('sipo2026'),
                'active' => true,
            ]
        );
        $admin->assignRole('admin');
    }
}
