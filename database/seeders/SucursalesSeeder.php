<?php

namespace Database\Seeders;

use App\Models\Configuracion;
use App\Models\InventarioSucursal;
use App\Models\Producto;
use App\Models\Sucursal;
use Illuminate\Database\Seeder;

class SucursalesSeeder extends Seeder
{
    /**
     * Garantiza que exista una sucursal principal y que todo el catálogo tenga
     * su fila de inventario en cada sucursal. Sin esto el POS muestra stock 0.
     * Es seguro ejecutarlo varias veces: nunca pisa stock ya existente.
     */
    public function run(): void
    {
        if (Sucursal::count() === 0) {
            $config = Configuracion::first();

            Sucursal::create([
                'nombre' => 'Sede Principal',
                'codigo' => 'SUC-001',
                'direccion' => $config->direccion ?? 'Av. Principal 123, Lima',
                'telefono' => $config->telefono ?? null,
                'ciudad' => 'Lima',
                'es_principal' => true,
                'estado' => 'activo',
            ]);
        }

        $principal = Sucursal::principal();
        $sucursales = Sucursal::all();

        Producto::query()->orderBy('id')->chunk(200, function ($productos) use ($sucursales, $principal) {
            foreach ($productos as $producto) {
                foreach ($sucursales as $sucursal) {
                    // El stock del catálogo se asigna a la sucursal principal; el resto inicia en 0
                    $esPrincipal = $principal && $sucursal->id === $principal->id;

                    InventarioSucursal::firstOrCreate(
                        ['producto_id' => $producto->id, 'sucursal_id' => $sucursal->id],
                        ['stock' => $esPrincipal ? max(0, (int)$producto->stock) : 0, 'stock_minimo' => 5]
                    );
                }

                $producto->sincronizarStockTotal();
            }
        });
    }
}
