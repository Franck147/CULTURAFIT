<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if (!$user->estaActivo()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Su cuenta ha sido inhabilitada. Contacte al administrador.',
            ]);
        }

        if (empty($roles)) {
            return $next($request);
        }

        if (!in_array($user->rol, $roles)) {
            session()->flash('acceso_denegado', 'No tienes los permisos necesarios para acceder a esta sección.');
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
