<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UsuariosController extends Controller
{
    public function index()
    {
        $config = Configuracion::getInfo();
        $usuarios = User::orderBy('id', 'desc')->get();
        return view('usuarios.index', compact('config', 'usuarios'));
    }

    public function crear()
    {
        $config = Configuracion::getInfo();
        return view('usuarios.crear', compact('config'));
    }

    public function guardar(Request $request)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'rol' => ['required', 'in:admin,cajero'],
        ]);

        User::create([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'rol' => $request->rol,
            'estado' => 'activo',
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado exitosamente.');
    }

    public function editar($id)
    {
        $config = Configuracion::getInfo();
        $usuario = User::findOrFail($id);
        return view('usuarios.editar', compact('config', 'usuario'));
    }

    public function actualizar(Request $request, $id)
    {
        $usuario = User::findOrFail($id);

        $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', 'unique:users,email,' . $usuario->id],
            'password' => ['nullable', 'string', 'min:6'],
            'rol' => ['required', 'in:admin,cajero'],
        ]);

        $usuario->nombre = $request->nombre;
        $usuario->email = $request->email;
        $usuario->rol = $request->rol;

        if (!empty($request->password)) {
            $usuario->password = Hash::make($request->password);
        }

        $usuario->save();

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado con éxito.');
    }

    public function cambiarEstado($id)
    {
        if ($id == Auth::id()) {
            return redirect()->route('usuarios.index')->with('error', 'Por seguridad, no puedes inhabilitar tu propia cuenta.');
        }

        $usuario = User::findOrFail($id);
        $usuario->estado = $usuario->estado === 'activo' ? 'inactivo' : 'activo';
        $usuario->save();

        return redirect()->route('usuarios.index')->with('success', 'Estado del usuario actualizado.');
    }
}
