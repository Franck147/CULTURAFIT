<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\Configuracion;
use App\Models\Gasto;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GastosController extends Controller
{
    public function index()
    {
        $config = Configuracion::getInfo();
        $gastos = Gasto::with(['usuario', 'caja'])->orderBy('id', 'desc')->get();
        return view('gastos.index', compact('config', 'gastos'));
    }

    public function crear()
    {
        $config = Configuracion::getInfo();
        return view('gastos.crear', compact('config'));
    }

    public function guardar(Request $request)
    {
        $request->validate([
            'descripcion' => ['required', 'string', 'max:255'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'fecha' => ['required', 'date'],
        ]);

        $user = Auth::user();
        $cajaAbierta = Caja::obtenerCajaAbierta($user->id);

        Gasto::create([
            'caja_id' => $cajaAbierta ? $cajaAbierta->id : null,
            'usuario_id' => $user->id,
            'descripcion' => $request->descripcion,
            'monto' => $request->monto,
            'fecha' => $request->fecha,
            'estado' => 'creado',
        ]);

        return redirect()->route('gastos.index')->with('success', 'Gasto registrado correctamente.');
    }

    public function anular(Request $request)
    {
        $request->validate([
            'id' => ['required', 'exists:gastos,id'],
            'motivo_anulacion' => ['required', 'string', 'max:255'],
        ]);

        $gasto = Gasto::findOrFail($request->id);
        $gasto->update([
            'estado' => 'anulado',
            'motivo_anulacion' => $request->motivo_anulacion,
        ]);

        return redirect()->route('gastos.index')->with('success', 'Gasto anulado. Ya no se contabilizará en los balances.');
    }
}
