<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventario_sucursales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->foreignId('sucursal_id')->constrained('sucursales')->onDelete('cascade');
            $table->integer('stock')->default(0);
            $table->integer('stock_minimo')->default(5);
            $table->timestamps();

            $table->unique(['producto_id', 'sucursal_id']);
        });

        // Población inicial: sincronizar stock de productos por sucursal
        $sucursales = DB::table('sucursales')->get();
        $principal = $sucursales->firstWhere('es_principal', true) ?? $sucursales->first();
        $productos = DB::table('productos')->get();

        foreach ($productos as $producto) {
            foreach ($sucursales as $sucursal) {
                $stockInicial = ($principal && $sucursal->id == $principal->id) ? (int)$producto->stock : 0;
                DB::table('inventario_sucursales')->insert([
                    'producto_id' => $producto->id,
                    'sucursal_id' => $sucursal->id,
                    'stock' => $stockInicial,
                    'stock_minimo' => 5,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario_sucursales');
    }
};
