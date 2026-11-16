<?php

namespace Database\Seeders;

use App\Models\Socio;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SociosSeeder extends Seeder
{
    public function run(): void
    {
        $socios = [
            [
                'id' => 1,
                'nombre' => 'Franck Adler Bautista Cisneros',
                'dni' => '74985447',
                'email' => 'adlercisneros147@gmail.com',
                'telefono' => '982424917',
                'estado' => 'activo',
                'foto' => null,
                'fecha_registro' => Carbon::now(),
            ],
            [
                'id' => 2,
                'nombre' => 'yandi cano',
                'dni' => '12345678',
                'email' => 'yandi@culturafit.com',
                'telefono' => '987654321',
                'estado' => 'activo',
                'foto' => null,
                'fecha_registro' => Carbon::now(),
            ],
        ];

        foreach ($socios as $s) {
            Socio::updateOrCreate(['id' => $s['id']], $s);
        }
    }
}
