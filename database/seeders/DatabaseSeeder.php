<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UsersSeeder::class,
            ConfiguracionSeeder::class,
            CategoriasSeeder::class,
            ProductosSeeder::class,
            SociosSeeder::class,
        ]);

        // Debe ir después de productos: crea la sucursal principal y el inventario por sucursal
        $this->call(SucursalesSeeder::class);

        // Sincronizar secuencias automáticas de PostgreSQL con los IDs importados
        if (config('database.default') === 'pgsql') {
            DB::statement("SELECT setval('categorias_id_seq', COALESCE((SELECT MAX(id) FROM categorias), 1))");
            DB::statement("SELECT setval('productos_id_seq', COALESCE((SELECT MAX(id) FROM productos), 1))");
            DB::statement("SELECT setval('socios_id_seq', COALESCE((SELECT MAX(id) FROM socios), 1))");
        }
    }
}
