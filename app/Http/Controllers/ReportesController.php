<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\Gasto;
use App\Models\Socio;
use App\Models\User;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportesController extends Controller
{
    public function index(Request $request)
    {
        $config = Configuracion::getInfo();

        // 1. Manejo de Filtros y Presets de Fecha
        $preset = $request->input('preset');
        $ahora = Carbon::now();

        if ($preset === 'hoy') {
            $desde = $ahora->format('Y-m-d');
            $hasta = $ahora->format('Y-m-d');
        } elseif ($preset === 'ayer') {
            $desde = $ahora->copy()->subDay()->format('Y-m-d');
            $hasta = $ahora->copy()->subDay()->format('Y-m-d');
        } elseif ($preset === 'semana') {
            $desde = $ahora->copy()->startOfWeek()->format('Y-m-d');
            $hasta = $ahora->format('Y-m-d');
        } elseif ($preset === 'mes') {
            $desde = $ahora->copy()->startOfMonth()->format('Y-m-d');
            $hasta = $ahora->copy()->endOfMonth()->format('Y-m-d');
        } elseif ($preset === 'mes_anterior') {
            $desde = $ahora->copy()->subMonth()->startOfMonth()->format('Y-m-d');
            $hasta = $ahora->copy()->subMonth()->endOfMonth()->format('Y-m-d');
        } elseif ($preset === 'anio') {
            $desde = $ahora->copy()->startOfYear()->format('Y-m-d');
            $hasta = $ahora->format('Y-m-d');
        } else {
            $desde = $request->input('desde', $ahora->copy()->startOfMonth()->format('Y-m-d'));
            $hasta = $request->input('hasta', $ahora->format('Y-m-d'));
        }

        $cajeroId = $request->input('cajero_id');
        $metodoPago = $request->input('metodo_pago');

        // 2. Lista de Cajeros / Usuarios para el selector
        $cajeros = User::whereIn('rol', ['admin', 'cajero'])
            ->orderBy('nombre', 'asc')
            ->get();

        // 3. Query Base de Ventas con Filtros
        $ventasQuery = Venta::with(['caja.usuario', 'socio', 'detalles.producto'])
            ->whereDate('fecha', '>=', $desde)
            ->whereDate('fecha', '<=', $hasta);

        if (!empty($cajeroId)) {
            $ventasQuery->whereHas('caja', function ($q) use ($cajeroId) {
                $q->where('usuario_id', $cajeroId);
            });
        }

        if (!empty($metodoPago)) {
            $ventasQuery->where('metodo_pago', $metodoPago);
        }

        // Obtener colección completa filtrada para cálculos y tablas
        $ventas = (clone $ventasQuery)->orderBy('fecha', 'desc')->get();

        // 4. Totales y Métricas Financieras Principales
        $totalVentasNetas = (float)$ventas->sum('total');
        $totalDescuentos = (float)$ventas->sum('descuento');
        $totalVentasBrutas = $totalVentasNetas + $totalDescuentos;
        $cantVentas = $ventas->count();
        $ticketPromedio = $cantVentas > 0 ? $totalVentasNetas / $cantVentas : 0;

        // 5. Gastos en el período (solo no anulados)
        $gastosQuery = Gasto::with(['usuario', 'caja'])
            ->whereDate('fecha', '>=', $desde)
            ->whereDate('fecha', '<=', $hasta)
            ->where('estado', '!=', 'anulado');

        if (!empty($cajeroId)) {
            $gastosQuery->where('usuario_id', $cajeroId);
        }

        $gastos = $gastosQuery->orderBy('fecha', 'desc')->get();
        $totalGastos = (float)$gastos->sum('monto');
        $gastosOperativos = (float)$gastos->whereNull('venta_id')->sum('monto');
        $gastosDescuentos = (float)$gastos->whereNotNull('venta_id')->sum('monto');

        // Utilidad Neta real = Ventas Netas - Gastos Operativos (equivalente a Ventas Brutas - Total Gastos)
        $utilidadNeta = $totalVentasNetas - $gastosOperativos;
        $margenRentabilidad = $totalVentasNetas > 0 ? ($utilidadNeta / $totalVentasNetas) * 100 : 0;
        $sociosActivos = Socio::where('estado', 'activo')->count();

        // 6. Desglose Detallado por Cajero (Auditoría de Rendimiento)
        $rendimientoCajeros = [];
        $ventasAgrupadasPorCajero = $ventas->groupBy(function ($v) {
            return $v->caja && $v->caja->usuario ? $v->caja->usuario->id : 0;
        });

        foreach ($ventasAgrupadasPorCajero as $uid => $grupo) {
            $usuario = $grupo->first()->caja->usuario ?? null;
            $nombreCajero = $usuario ? $usuario->nombre : 'Sin Cajero / Venta Directa';
            $rolCajero = $usuario ? $usuario->rol : 'Sistema';
            $emailCajero = $usuario ? $usuario->email : '-';

            $totalCajero = (float)$grupo->sum('total');
            $cantCajero = $grupo->count();
            $efectivoCajero = (float)$grupo->where('metodo_pago', 'efectivo')->sum('total');
            $tarjetaCajero = (float)$grupo->where('metodo_pago', 'tarjeta')->sum('total');
            $transfCajero = (float)$grupo->where('metodo_pago', 'transferencia')->sum('total');

            $rendimientoCajeros[] = [
                'usuario_id' => $uid,
                'nombre' => $nombreCajero,
                'rol' => $rolCajero,
                'email' => $emailCajero,
                'cantidad_ventas' => $cantCajero,
                'total_recaudado' => $totalCajero,
                'ticket_promedio' => $cantCajero > 0 ? $totalCajero / $cantCajero : 0,
                'efectivo' => $efectivoCajero,
                'tarjeta' => $tarjetaCajero,
                'transferencia' => $transfCajero,
                'participacion_porc' => $totalVentasNetas > 0 ? ($totalCajero / $totalVentasNetas) * 100 : 0,
            ];
        }

        // Ordenar cajeros por mayor facturación
        usort($rendimientoCajeros, fn($a, $b) => $b['total_recaudado'] <=> $a['total_recaudado']);

        // 7. Métricas por Método de Pago
        $metodosTotales = [
            'efectivo' => [
                'total' => (float)$ventas->where('metodo_pago', 'efectivo')->sum('total'),
                'cantidad' => $ventas->where('metodo_pago', 'efectivo')->count(),
            ],
            'tarjeta' => [
                'total' => (float)$ventas->where('metodo_pago', 'tarjeta')->sum('total'),
                'cantidad' => $ventas->where('metodo_pago', 'tarjeta')->count(),
            ],
            'transferencia' => [
                'total' => (float)$ventas->where('metodo_pago', 'transferencia')->sum('total'),
                'cantidad' => $ventas->where('metodo_pago', 'transferencia')->count(),
            ],
        ];

        // 8. Gráfico de Tendencia Diaria (Compatible con PostgreSQL y MySQL)
        $isPgsql = config('database.default') === 'pgsql';
        $diaKeyExpr = $isPgsql ? "TO_CHAR(fecha, 'YYYY-MM-DD')" : "DATE_FORMAT(fecha, '%Y-%m-%d')";
        $diaNomExpr = $isPgsql ? "TO_CHAR(fecha, 'DD/MM')" : "DATE_FORMAT(fecha, '%d/%m')";

        $chartDiarioQuery = Venta::selectRaw("{$diaKeyExpr} as dia_key, {$diaNomExpr} as dia_nombre, SUM(total) as total")
            ->whereDate('fecha', '>=', $desde)
            ->whereDate('fecha', '<=', $hasta);

        if (!empty($cajeroId)) {
            $chartDiarioQuery->whereHas('caja', function ($q) use ($cajeroId) {
                $q->where('usuario_id', $cajeroId);
            });
        }
        if (!empty($metodoPago)) {
            $chartDiarioQuery->where('metodo_pago', $metodoPago);
        }

        $chartDiario = $chartDiarioQuery->groupByRaw("dia_key, dia_nombre")
            ->orderByRaw("dia_key ASC")
            ->get();

        $chartLabels = $chartDiario->pluck('dia_nombre')->toArray();
        $chartData = $chartDiario->pluck('total')->map(fn($v) => (float)$v)->toArray();

        return view('reportes.index', compact(
            'config',
            'desde',
            'hasta',
            'preset',
            'cajeroId',
            'metodoPago',
            'cajeros',
            'ventas',
            'gastos',
            'totalVentasBrutas',
            'totalDescuentos',
            'totalVentasNetas',
            'cantVentas',
            'ticketPromedio',
            'totalGastos',
            'utilidadNeta',
            'margenRentabilidad',
            'sociosActivos',
            'rendimientoCajeros',
            'metodosTotales',
            'chartLabels',
            'chartData'
        ));
    }

    /**
     * Exporta el listado de ventas filtrado directamente a un archivo CSV estructurado para Excel
     */
    public function exportarExcel(Request $request)
    {
        $desde = $request->input('desde', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $hasta = $request->input('hasta', Carbon::now()->format('Y-m-d'));
        $cajeroId = $request->input('cajero_id');
        $metodoPago = $request->input('metodo_pago');

        $query = Venta::with(['caja.usuario', 'socio', 'detalles.producto'])
            ->whereDate('fecha', '>=', $desde)
            ->whereDate('fecha', '<=', $hasta);

        if (!empty($cajeroId)) {
            $query->whereHas('caja', function ($q) use ($cajeroId) {
                $q->where('usuario_id', $cajeroId);
            });
        }

        if (!empty($metodoPago)) {
            $query->where('metodo_pago', $metodoPago);
        }

        $ventas = $query->orderBy('fecha', 'desc')->get();

        $filename = "reporte_ventas_{$desde}_al_{$hasta}.csv";

        $response = new StreamedResponse(function () use ($ventas) {
            $handle = fopen('php://output', 'w');

            // BOM UTF-8 para compatibilidad absoluta con Microsoft Excel (tildes y símbolos)
            fputs($handle, "\xEF\xBB\xBF");

            // Encabezados de columnas
            fputcsv($handle, [
                'NRO TICKET',
                'FECHA',
                'HORA',
                'CAJERO',
                'ROL CAJERO',
                'CLIENTE / SOCIO',
                'DNI CLIENTE',
                'METODO DE PAGO',
                'SUBTOTAL',
                'DESCUENTO',
                'TOTAL NETO',
                'CANT. ARTICULOS',
                'DETALLE DE PRODUCTOS'
            ], ';');

            foreach ($ventas as $v) {
                $cajeroNombre = $v->caja && $v->caja->usuario ? $v->caja->usuario->nombre : 'N/A';
                $cajeroRol = $v->caja && $v->caja->usuario ? ucfirst($v->caja->usuario->rol) : 'N/A';
                $clienteNombre = $v->socio ? $v->socio->nombre : 'Venta Directa';
                $clienteDni = $v->socio ? $v->socio->dni : '-';

                $subtotal = $v->total + $v->descuento;

                $cantArticulos = $v->detalles->sum('cantidad');
                $prodsTexto = $v->detalles->map(function ($d) {
                    $prodNom = $d->producto ? $d->producto->nombre : 'Item';
                    return "{$d->cantidad}x {$prodNom}";
                })->implode(' | ');

                fputcsv($handle, [
                    '#' . str_pad($v->id, 6, '0', STR_PAD_LEFT),
                    $v->fecha->format('d/m/Y'),
                    $v->fecha->format('H:i:s'),
                    $cajeroNombre,
                    $cajeroRol,
                    $clienteNombre,
                    $clienteDni,
                    strtoupper($v->metodo_pago_nombre),
                    number_format($subtotal, 2, '.', ''),
                    number_format($v->descuento, 2, '.', ''),
                    number_format($v->total, 2, '.', ''),
                    $cantArticulos,
                    $prodsTexto
                ], ';');
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$filename}\"");

        return $response;
    }
}
