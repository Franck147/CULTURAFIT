<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriasSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            ['id' => 1, 'nombre' => 'Dragon Pharma', 'estado' => 'inactivo'],
            ['id' => 2, 'nombre' => 'Ultimate Nutrition', 'estado' => 'activo'],
            ['id' => 3, 'nombre' => 'Optimum Nutrition', 'estado' => 'inactivo'],
            ['id' => 4, 'nombre' => '1.- Proteínas Nacionales', 'estado' => 'activo'],
            ['id' => 5, 'nombre' => 'Roni Colleman', 'estado' => 'inactivo'],
            ['id' => 6, 'nombre' => 'Lvl Pro', 'estado' => 'activo'],
            ['id' => 7, 'nombre' => 'Dymatize', 'estado' => 'inactivo'],
            ['id' => 8, 'nombre' => 'Applied Nutrition', 'estado' => 'inactivo'],
            ['id' => 9, 'nombre' => 'C4', 'estado' => 'inactivo'],
            ['id' => 10, 'nombre' => 'Innova Nutrition', 'estado' => 'inactivo'],
            ['id' => 11, 'nombre' => 'Insane Labs', 'estado' => 'inactivo'],
            ['id' => 12, 'nombre' => 'Kevin Levrone', 'estado' => 'inactivo'],
            ['id' => 13, 'nombre' => 'Scitec Nutrition', 'estado' => 'inactivo'],
            ['id' => 14, 'nombre' => 'Yava Labs', 'estado' => 'inactivo'],
            ['id' => 15, 'nombre' => 'ProSupps', 'estado' => 'inactivo'],
            ['id' => 16, 'nombre' => 'Pre entrenos', 'estado' => 'activo'],
            ['id' => 17, 'nombre' => 'JUGOS', 'estado' => 'activo'],
            ['id' => 18, 'nombre' => 'PROTEINAS IMPORTADAS', 'estado' => 'activo'],
        ];

        foreach ($categorias as $c) {
            Categoria::updateOrCreate(['id' => $c['id']], $c);
        }
    }
}
