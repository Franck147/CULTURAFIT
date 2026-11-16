<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ConfiguracionController extends Controller
{
    public function index()
    {
        $config = Configuracion::getInfo();
        return view('configuracion.index', compact('config'));
    }

    public function actualizar(Request $request)
    {
        $request->validate([
            'nombre_sistema' => ['required', 'string', 'max:100'],
            'ruc' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'moneda' => ['required', 'string', 'max:10'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'], // Validación estricta anti-RCE
        ]);

        $config = Configuracion::getInfo();

        $data = [
            'nombre_sistema' => $request->nombre_sistema,
            'ruc' => $request->ruc,
            'direccion' => $request->direccion,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'moneda' => $request->moneda,
        ];

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $fileName = 'logo_empresa.' . $file->extension();
            $file->move(public_path('img'), $fileName);
            $data['logo'] = $fileName;
        }

        $config->update($data);

        return redirect()->route('configuracion.index')->with('success', 'Configuración de la empresa actualizada exitosamente.');
    }
}
