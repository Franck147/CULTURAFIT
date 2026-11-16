<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UsersSeeder extends Seeder
{
    /**
     * Crea los usuarios iniciales SOLO si no existen (nunca pisa contraseñas ya cambiadas).
     *
     * - Local / testing: usa las contraseñas de ejemplo documentadas en el README.
     * - Producción: genera una contraseña aleatoria por usuario y la muestra una sola
     *   vez en la consola. Cámbiala desde el módulo Usuarios al primer ingreso.
     */
    public function run(): void
    {
        $esProduccion = app()->environment('production');

        $users = [
            ['nombre' => 'Administrador Piero', 'email' => 'admin@gym.com', 'rol' => 'admin', 'demo' => 'admin123'],
            ['nombre' => 'Cajero Principal', 'email' => 'cajero@gym.com', 'rol' => 'cajero', 'demo' => 'cajero123'],
        ];

        foreach ($users as $u) {
            if (User::where('email', $u['email'])->exists()) {
                continue;
            }

            $password = $esProduccion ? Str::password(16, symbols: false) : $u['demo'];

            // El modelo User aplica el cast 'hashed', no hace falta Hash::make
            User::create([
                'nombre' => $u['nombre'],
                'email' => $u['email'],
                'password' => $password,
                'rol' => $u['rol'],
                'estado' => 'activo',
            ]);

            if ($esProduccion && $this->command) {
                $this->command->warn("Usuario creado: {$u['email']}  |  Contraseña temporal: {$password}");
                $this->command->warn('Anótala ahora: no se volverá a mostrar. Cámbiala al primer ingreso.');
            }
        }
    }
}
