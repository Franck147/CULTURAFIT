<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\Configuracion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CajaController extends Controller
{
    public function index()
    {
        $config = Configuracion::getInfo();
        $user = Auth::user();

        $cajaAbierta = Caja::obtenerCajaAbierta($user->id);

        if ($cajaAbierta) {
            $totales = $cajaAbierta->calcularTotalesServidor();
            return view('caja.cierre', compact('config', 'cajaAbierta', 'totales'));
        }

        $historial = Caja::with('usuario')
            ->orderBy('id', 'desc')
            ->limit(30)
            ->get();

        return view('caja.apertura', compact('config', 'historial'));
    }

    public function abrir(Request $request)
    {
        $request->validate([
            'monto_inicial' => ['required', 'numeric', 'min:0'],
        ]);

        $user = Auth::user();

        if (Caja::obtenerCajaAbierta($user->id)) {
            return redirect()->route('caja.index')->with('error', 'Ya tienes una caja abierta.');
        }

        Caja::create([
            'usuario_id' => $user->id,
            'monto_inicial' => $request->monto_inicial,
            'fecha_apertura' => Carbon::now(),
            'estado' => 'abierta',
        ]);

        return redirect()->route('caja.index')->with('success', '¡Turno de caja iniciado exitosamente!');
    }

    public function cerrar(Request $request)
    {
        $request->validate([
            'caja_id' => ['required', 'exists:cajas,id'],
            'monto_fisico' => ['required', 'numeric', 'min:0'],
        ]);

        $caja = Caja::findOrFail($request->caja_id);

        if ($caja->estado !== 'abierta') {
            return redirect()->route('caja.index')->with('error', 'Esta caja ya se encuentra cerrada.');
        }

        // SEGURIDAD: Los totales y el saldo esperado se calculan en el servidor, jamás del cliente
        $totales = $caja->calcularTotalesServidor();

        $montoFisico = (float)$request->monto_fisico;
        $saldoEsperado = (float)$totales['saldo_esperado'];
        $diferencia = $montoFisico - $saldoEsperado;

        $caja->update([
            'monto_final' => $montoFisico,
            'total_ventas' => $totales['total_ventas'],
            'total_gastos' => $totales['total_gastos'],
            'diferencia' => $diferencia,
            'fecha_cierre' => Carbon::now(),
            'estado' => 'cerrada',
        ]);

        return redirect()->route('caja.index')->with('success', 'Turno cerrado correctamente. El arqueo ha sido registrado.');
    }
}
