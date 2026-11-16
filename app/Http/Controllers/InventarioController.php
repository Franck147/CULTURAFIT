<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Configuracion;
use App\Models\InventarioSucursal;
use App\Models\Producto;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InventarioController extends Controller
{
    // --- CATEGORÍAS ---
    public function categorias()
    {
        $config = Configuracion::getInfo();
        $categorias = Categoria::orderBy('nombre', 'asc')->get();
        return view('inventario.categorias', compact('config', 'categorias'));
    }

    public function guardarCategoria(Request $request)
    {
        $validated = $request->validate([
            'id' => ['nullable', 'exists:categorias,id'],
            'nombre' => ['required', 'string', 'max:100'],
        ]);

        Categoria::updateOrCreate(
            ['id' => $validated['id'] ?? null],
            ['nombre' => $validated['nombre'], 'estado' => 'activo']
        );

        return redirect()->route('inventario.categorias')->with('success', 'Categoría guardada correctamente.');
    }

    public function cambiarEstadoCategoria($id)
    {
        $categoria = Categoria::findOrFail($id);
        $categoria->estado = $categoria->estado === 'activo' ? 'inactivo' : 'activo';
        $categoria->save();

        return redirect()->route('inventario.categorias')->with('success', 'Estado de categoría actualizado.');
    }

    // --- PRODUCTOS ---
    public function productos()
    {
        $config = Configuracion::getInfo();
        $sucursales = Sucursal::where('estado', 'activo')->orderBy('es_principal', 'desc')->orderBy('nombre', 'asc')->get();
        $sucursalActivaId = session('sucursal_activa_id');

        $productos = Producto::with(['categoria', 'inventarios.sucursal'])->orderBy('nombre', 'asc')->get();
        $categorias = Categoria::where('estado', 'activo')->orderBy('nombre', 'asc')->get();

        return view('inventario.productos', compact('config', 'productos', 'categorias', 'sucursales', 'sucursalActivaId'));
    }

    public function guardarProducto(Request $request)
    {
        $validated = $request->validate([
            'id' => ['nullable', 'exists:productos,id'],
            'categoria_id' => ['required', 'exists:categorias,id'],
            'codigo' => ['nullable', 'string', 'max:50'],
            'nombre' => ['required', 'string', 'max:150'],
            'precio_compra' => ['required', 'numeric', 'min:0'],
            'precio_venta' => ['required', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'sucursal_id' => ['nullable', 'exists:sucursales,id'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'], // Validación estricta anti-RCE
        ]);

        $data = [
            'categoria_id' => $validated['categoria_id'],
            'codigo' => $validated['codigo'] ?? null,
            'nombre' => $validated['nombre'],
            'precio_compra' => $validated['precio_compra'],
            'precio_venta' => $validated['precio_venta'],
        ];

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $fileName = 'prod_' . time() . '_' . Str::random(6) . '.' . $file->extension();
            $file->move(public_path('img/productos'), $fileName);
            $data['foto'] = $fileName;
        }

        $esNuevo = empty($validated['id']);
        $producto = Producto::updateOrCreate(['id' => $validated['id'] ?? null], $data);

        // Si es producto nuevo, inicializar inventario en las sucursales
        if ($esNuevo) {
            $sucursales = Sucursal::where('estado', 'activo')->get();
            $targetSucursalId = $request->sucursal_id ?: (session('sucursal_activa_id') ?: optional(Sucursal::principal())->id);
            $stockInicial = max(0, (int)($request->stock ?? 0));

            foreach ($sucursales as $suc) {
                InventarioSucursal::updateOrCreate(
                    ['producto_id' => $producto->id, 'sucursal_id' => $suc->id],
                    ['stock' => ($suc->id == $targetSucursalId) ? $stockInicial : 0, 'stock_minimo' => 5]
                );
            }
            $producto->sincronizarStockTotal();
        }

        return redirect()->route('inventario.productos')->with('success', 'Producto guardado exitosamente.');
    }

    public function cambiarEstadoProducto($id)
    {
        $producto = Producto::findOrFail($id);
        $producto->estado = $producto->estado === 'activo' ? 'inactivo' : 'activo';
        $producto->save();

        return redirect()->route('inventario.productos')->with('success', 'Estado de producto modificado.');
    }

    public function ajusteStock(Request $request)
    {
        $request->validate([
            'producto_id' => ['required', 'exists:productos,id'],
            'sucursal_id' => ['nullable', 'exists:sucursales,id'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'operacion' => ['required', 'in:sumar,restar'],
        ]);

        $producto = Producto::findOrFail($request->producto_id);
        $sucursalId = $request->sucursal_id ?: (session('sucursal_activa_id') ?: optional(Sucursal::principal())->id);
        $sucursal = Sucursal::findOrFail($sucursalId);

        $inv = InventarioSucursal::firstOrCreate(
            ['producto_id' => $producto->id, 'sucursal_id' => $sucursalId],
            ['stock' => 0, 'stock_minimo' => 5]
        );

        if ($request->operacion === 'restar' && $inv->stock < $request->cantidad) {
            return redirect()->route('inventario.productos')->with('error', "No puedes restar {$request->cantidad} unidades. Stock disponible en {$sucursal->nombre}: {$inv->stock}.");
        }

        if ($request->operacion === 'sumar') {
            $inv->increment('stock', $request->cantidad);
        } else {
            $inv->decrement('stock', $request->cantidad);
        }

        $producto->sincronizarStockTotal();

        return redirect()->route('inventario.productos')->with('success', "Stock de '{$producto->nombre}' actualizado con éxito en {$sucursal->nombre} (Nuevo stock local: {$inv->stock}).");
    }
}
