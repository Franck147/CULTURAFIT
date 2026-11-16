<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\Medida;
use App\Models\Rutina;
use App\Models\Socio;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProgresoController extends Controller
{
    public function ver($socioId)
    {
        return redirect()->route('socios.ventas', $socioId);
    }

    public function guardarMedida(Request $request)
    {
        $validated = $request->validate([
            'socio_id' => ['required', 'exists:socios,id'],
            'peso' => ['required', 'numeric', 'min:20', 'max:300'],
            'grasa' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'cintura' => ['nullable', 'numeric', 'min:20', 'max:250'],
            'brazo' => ['nullable', 'numeric', 'min:10', 'max:100'],
            'fecha' => ['required', 'date'],
        ]);

        Medida::create($validated);

        return redirect()->route('progreso.ver', $request->socio_id)
            ->with('success', 'Medida registrada exitosamente.');
    }

    public function guardarRutina(Request $request)
    {
        $validated = $request->validate([
            'socio_id' => ['required', 'exists:socios,id'],
            'dia1' => ['nullable', 'string'],
            'dia2' => ['nullable', 'string'],
            'dia3' => ['nullable', 'string'],
            'dia4' => ['nullable', 'string'],
            'dia5' => ['nullable', 'string'],
            'dia6' => ['nullable', 'string'],
            'observaciones' => ['nullable', 'string'],
        ]);

        Rutina::create(array_merge($validated, ['fecha_asignacion' => Carbon::now()]));

        return redirect()->route('progreso.ver', ['socioId' => $request->socio_id, 'tab' => 'rutina'])
            ->with('success', 'Rutina de entrenamiento actualizada.');
    }

    public function eliminarMedida($id)
    {
        $medida = Medida::findOrFail($id);
        $socioId = $medida->socio_id;
        $medida->delete();

        return redirect()->route('progreso.ver', $socioId)->with('success', 'Medida eliminada.');
    }
}
