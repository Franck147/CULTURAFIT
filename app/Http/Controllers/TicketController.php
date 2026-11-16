<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\Venta;

class TicketController extends Controller
{
    public function ver($id)
    {
        $config = Configuracion::getInfo();
        $venta = Venta::with(['detalles.producto', 'socio', 'caja.usuario', 'sucursal'])->findOrFail($id);

        return view('pos.ticket', compact('config', 'venta'));
    }
}
