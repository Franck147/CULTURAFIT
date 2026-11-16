<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\InventarioSucursal;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Http\Request;

class SucursalesController extends Controller
{
    public function index()
    {
        $config = Configuracion::getInfo();
        $sucursales = Sucursal::withCount(['cajeros', 'ventas'])
            ->orderBy('es_principal', 'desc')
            ->orderBy('id', 'asc')
            ->get();

        $totalSucursales = $sucursales->count();
        $sucursalesActivas = $sucursales->where('estado', 'activo')->count();
        $totalCajeros = User::where('rol', 'cajero')->count();
        $cajerosAsignados = User::where('rol', 'cajero')->whereNotNull('sucursal_id')->count();

        return view('sucursales.index', compact(
            'config',
            'sucursales',
            'totalSucursales',
            'sucursalesActivas',
            'totalCajeros',
            'cajerosAsignados'
        ));
    }

    public function crear()
    {
        $config = Configuracion::getInfo();
        return view('sucursales.crear', compact('config'));
    }

    public function guardar(Request $request)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'codigo' => ['nullable', 'string', 'max:50', 'unique:sucursales,codigo'],
            'direccion' => ['required', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'ciudad' => ['nullable', 'string', 'max:100'],
            'estado' => ['required', 'in:activo,inactivo'],
        ]);

        $esPrincipal = $request->has('es_principal');

        if ($esPrincipal) {
            Sucursal::where('es_principal', true)->update(['es_principal' => false]);
        }

        $sucursal = Sucursal::create([
            'nombre' => $request->nombre,
            'codigo' => $request->codigo ?: null,
            'direccion' => $request->direccion,
            'telefono' => $request->telefono ?: null,
            'ciudad' => $request->ciudad ?: 'Lima',
            'es_principal' => $esPrincipal,
            'estado' => $request->estado,
        ]);

        // Registrar todo el catálogo de productos existente en la nueva sucursal con stock 0
        $productos = Producto::all();
        foreach ($productos as $p) {
            InventarioSucursal::firstOrCreate(
                ['producto_id' => $p->id, 'sucursal_id' => $sucursal->id],
                ['stock' => 0, 'stock_minimo' => 5]
            );
        }

        return redirect()->route('sucursales.index')->with('success', 'Sucursal registrada con éxito y catálogo de productos sincronizado.');
    }

    public function editar($id)
    {
        $config = Configuracion::getInfo();
        $sucursal = Sucursal::with('cajeros')->findOrFail($id);
        return view('sucursales.editar', compact('config', 'sucursal'));
    }

    public function actualizar(Request $request, $id)
    {
        $sucursal = Sucursal::findOrFail($id);

        $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'codigo' => ['nullable', 'string', 'max:50', 'unique:sucursales,codigo,' . $sucursal->id],
            'direccion' => ['required', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'ciudad' => ['nullable', 'string', 'max:100'],
            'estado' => ['required', 'in:activo,inactivo'],
        ]);

        $esPrincipal = $request->has('es_principal');

        if ($esPrincipal && !$sucursal->es_principal) {
            Sucursal::where('id', '!=', $sucursal->id)->update(['es_principal' => false]);
            $sucursal->es_principal = true;
        } elseif (!$esPrincipal && $sucursal->es_principal) {
            // No permitir dejar sin sucursal principal
            $otro = Sucursal::where('id', '!=', $sucursal->id)->where('estado', 'activo')->first();
            if ($otro) {
                $otro->update(['es_principal' => true]);
                $sucursal->es_principal = false;
            }
        }

        $sucursal->nombre = $request->nombre;
        $sucursal->codigo = $request->codigo ?: null;
        $sucursal->direccion = $request->direccion;
        $sucursal->telefono = $request->telefono ?: null;
        $sucursal->ciudad = $request->ciudad ?: 'Lima';
        $sucursal->estado = $request->estado;
        $sucursal->save();

        return redirect()->route('sucursales.index')->with('success', 'Datos de la sucursal actualizados.');
    }

    public function cambiarEstado($id)
    {
        $sucursal = Sucursal::findOrFail($id);

        if ($sucursal->es_principal && $sucursal->estado === 'activo') {
            return redirect()->route('sucursales.index')->with('error', 'No puedes desactivar la sucursal principal. Asigna otra sucursal como principal primero.');
        }

        $sucursal->estado = $sucursal->estado === 'activo' ? 'inactivo' : 'activo';
        $sucursal->save();

        return redirect()->route('sucursales.index')->with('success', 'Estado de la sucursal cambiado.');
    }

    public function cambiarContexto(Request $request)
    {
        $sucursalId = $request->input('sucursal_contexto_id');

        if (empty($sucursalId) || $sucursalId === 'todas') {
            session()->forget('sucursal_activa_id');
            return back()->with('success', 'Mostrando datos consolidados de: Todas las Sucursales (Visión Global).');
        }

        $sucursal = Sucursal::where('estado', 'activo')->findOrFail($sucursalId);
        session(['sucursal_activa_id' => $sucursal->id]);

        return back()->with('success', "Has ingresado al contexto de la tienda: {$sucursal->nombre}.");
    }
}
