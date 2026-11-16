<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'nombre' => 'Administrador Piero',
                'email' => 'admin@gym.com',
                'password' => Hash::make('admin123'),
                'rol' => 'admin',
                'estado' => 'activo',
            ],
            [
                'nombre' => 'Cajero Principal',
                'email' => 'cajero@gym.com',
                'password' => Hash::make('cajero123'),
                'rol' => 'cajero',
                'estado' => 'activo',
            ],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(['email' => $u['email']], $u);
        }
    }
}
