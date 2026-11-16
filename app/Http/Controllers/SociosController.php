<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\DetalleVenta;
use App\Models\Socio;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SociosController extends Controller
{
    public function index()
    {
        $config = Configuracion::getInfo();
        $socios = Socio::orderBy('id', 'desc')->get();
        return view('socios.index', compact('config', 'socios'));
    }

    public function crear()
    {
        $config = Configuracion::getInfo();
        return view('socios.crear', compact('config'));
    }

    public function guardar(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'dni' => ['required', 'string', 'max:20', 'unique:socios,dni'],
            'email' => ['nullable', 'email', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'estado' => ['required', 'in:activo,inactivo,pendiente'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'], // Validación estricta anti-RCE
        ]);

        $data = [
            'nombre' => $validated['nombre'],
            'dni' => $validated['dni'],
            'email' => $validated['email'] ?? null,
            'telefono' => $validated['telefono'] ?? null,
            'estado' => $validated['estado'],
            'fecha_registro' => Carbon::now(),
        ];

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $fileName = 'socio_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('img/socios'), $fileName);
            $data['foto'] = $fileName;
        }

        Socio::create($data);

        return redirect()->route('socios.index')->with('success', 'Socio registrado con éxito.');
    }

    public function editar($id)
    {
        $config = Configuracion::getInfo();
        $socio = Socio::findOrFail($id);
        return view('socios.editar', compact('config', 'socio'));
    }

    public function actualizar(Request $request, $id)
    {
        $socio = Socio::findOrFail($id);

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'dni' => ['required', 'string', 'max:20', 'unique:socios,dni,' . $socio->id],
            'email' => ['nullable', 'email', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'estado' => ['required', 'in:activo,inactivo,pendiente'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $socio->nombre = $validated['nombre'];
        $socio->dni = $validated['dni'];
        $socio->email = $validated['email'] ?? null;
        $socio->telefono = $validated['telefono'] ?? null;
        $socio->estado = $validated['estado'];

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $fileName = 'socio_upd_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('img/socios'), $fileName);
            $socio->foto = $fileName;
        }

        $socio->save();

        return redirect()->route('socios.index')->with('success', 'Datos del socio actualizados.');
    }

    public function cambiarEstado($id)
    {
        $socio = Socio::findOrFail($id);
        $socio->estado = $socio->estado === 'activo' ? 'inactivo' : 'activo';
        $socio->save();

        return redirect()->route('socios.index')->with('success', 'Estado del socio cambiado.');
    }

    /**
     * Muestra el historial completo de ventas / compras realizadas a un cliente.
     */
    public function ventas($socioId)
    {
        $config = Configuracion::getInfo();
        $socio = Socio::findOrFail($socioId);

        $ventas = Venta::with(['caja.usuario', 'detalles.producto'])
            ->where('socio_id', $socioId)
            ->orderBy('fecha', 'desc')
            ->paginate(15);

        $totalComprado = (float) Venta::where('socio_id', $socioId)->sum('total');
        $cantVentas = Venta::where('socio_id', $socioId)->count();
        $ticketPromedio = $cantVentas > 0 ? $totalComprado / $cantVentas : 0;
        $ultimaVenta = Venta::where('socio_id', $socioId)->orderBy('fecha', 'desc')->first();

        // Top suplementos/productos adquiridos por este cliente
        $topProductos = DetalleVenta::whereHas('venta', function ($q) use ($socioId) {
                $q->where('socio_id', $socioId);
            })
            ->select('producto_id', DB::raw('SUM(cantidad) as total_cantidad'), DB::raw('SUM(subtotal) as total_gastado'))
            ->with('producto')
            ->groupBy('producto_id')
            ->orderByDesc('total_cantidad')
            ->limit(5)
            ->get();

        return view('socios.ventas', compact(
            'config',
            'socio',
            'ventas',
            'totalComprado',
            'cantVentas',
            'ticketPromedio',
            'ultimaVenta',
            'topProductos'
        ));
    }
}
