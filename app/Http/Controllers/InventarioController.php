<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Configuracion;
use App\Models\Producto;
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
        $productos = Producto::with('categoria')->orderBy('nombre', 'asc')->get();
        $categorias = Categoria::where('estado', 'activo')->orderBy('nombre', 'asc')->get();

        return view('inventario.productos', compact('config', 'productos', 'categorias'));
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
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'], // Validación estricta anti-RCE
        ]);

        $data = [
            'categoria_id' => $validated['categoria_id'],
            'codigo' => $validated['codigo'] ?? null,
            'nombre' => $validated['nombre'],
            'precio_compra' => $validated['precio_compra'],
            'precio_venta' => $validated['precio_venta'],
            'stock' => $validated['stock'] ?? 0,
        ];

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $fileName = 'prod_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('img/productos'), $fileName);
            $data['foto'] = $fileName;
        }

        Producto::updateOrCreate(['id' => $validated['id'] ?? null], $data);

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
            'cantidad' => ['required', 'integer', 'min:1'],
            'operacion' => ['required', 'in:sumar,restar'],
        ]);

        $producto = Producto::findOrFail($request->producto_id);

        if ($request->operacion === 'restar' && $producto->stock < $request->cantidad) {
            return redirect()->route('inventario.productos')->with('error', 'No puedes restar más stock del existente.');
        }

        if ($request->operacion === 'sumar') {
            $producto->increment('stock', $request->cantidad);
        } else {
            $producto->decrement('stock', $request->cantidad);
        }

        return redirect()->route('inventario.productos')->with('success', 'Stock ajustado con éxito.');
    }
}
