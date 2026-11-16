<?php

namespace Database\Seeders;

use App\Models\Configuracion;
use Illuminate\Database\Seeder;

class ConfiguracionSeeder extends Seeder
{
    public function run(): void
    {
        Configuracion::updateOrCreate(
            ['id' => 1],
            [
                'nombre_sistema' => 'CULTURA FIT',
                'ruc' => '20600000001',
                'direccion' => 'Av. Principal 123, Lima',
                'telefono' => '987654321',
                'email' => 'admin@gym.com',
                'logo' => 'logo_empresa.png',
                'moneda' => 'S/',
            ]
        );
    }
}
