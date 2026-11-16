<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\DetalleVenta;
use App\Models\Gasto;
use App\Models\Producto;
use App\Models\Socio;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $config = Configuracion::getInfo();

        $sucursalId = session('sucursal_activa_id');

        // 1. KPIs
        $totalSocios = Socio::where('estado', 'activo')->count();

        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();

        $ingresosQuery = Venta::whereBetween('fecha', [$inicioMes, $finMes]);
        $gastosQuery = Gasto::whereBetween('fecha', [$inicioMes->format('Y-m-d'), $finMes->format('Y-m-d')])
            ->where('estado', '!=', 'anulado')
            // Solo salidas reales de dinero: los descuentos de tickets ya están restados en el total de cada venta
            ->whereNull('venta_id');

        if ($sucursalId) {
            $ingresosQuery->where('sucursal_id', $sucursalId);
            $gastosQuery->whereHas('caja', fn($q) => $q->where('sucursal_id', $sucursalId));
        }

        $ingresosMes = (float)$ingresosQuery->sum('total');
        $gastosMes = (float)$gastosQuery->sum('monto');
        $utilidadMes = $ingresosMes - $gastosMes;

        // 2. Ventas recientes
        $ventasRecientesQuery = Venta::with(['socio', 'caja.usuario', 'sucursal'])
            ->orderBy('fecha', 'desc')
            ->limit(5);

        if ($sucursalId) {
            $ventasRecientesQuery->where('sucursal_id', $sucursalId);
        }

        $ventasRecientes = $ventasRecientesQuery->get();

        // 3. Alertas de inventario
        if ($sucursalId) {
            $bajoStock = Producto::whereHas('inventarios', function($q) use ($sucursalId) {
                $q->where('sucursal_id', $sucursalId)->where('stock', '<=', 10);
            })->where('estado', 'activo')
              ->with(['inventarios' => fn($q) => $q->where('sucursal_id', $sucursalId)])
              ->limit(5)
              ->get();
        } else {
            $bajoStock = Producto::where('stock', '<=', 10)
                ->where('estado', 'activo')
                ->orderBy('stock', 'asc')
                ->limit(5)
                ->get();
        }

        // 4. Gráfico Ventas últimos 6 meses (Compatible con PostgreSQL y MySQL)
        $isPgsql = config('database.default') === 'pgsql';
        $mesKeyExpr = $isPgsql ? "TO_CHAR(fecha, 'YYYY-MM')" : "DATE_FORMAT(fecha, '%Y-%m')";
        $mesNomExpr = $isPgsql ? "TO_CHAR(fecha, 'Mon YYYY')" : "DATE_FORMAT(fecha, '%b %Y')";
        $intervalExpr = $isPgsql ? "NOW() - INTERVAL '6 months'" : "DATE_SUB(NOW(), INTERVAL 6 MONTH)";

        $ventasMesesQuery = Venta::selectRaw("{$mesKeyExpr} as mes_key, {$mesNomExpr} as mes_nombre, SUM(total) as total")
            ->whereRaw("fecha >= {$intervalExpr}");

        if ($sucursalId) {
            $ventasMesesQuery->where('sucursal_id', $sucursalId);
        }

        $ventasMeses = $ventasMesesQuery->groupByRaw("mes_key, mes_nombre")
            ->orderByRaw("mes_key ASC")
            ->get();

        // Siempre 6 meses en el gráfico (los que no tienen ventas van en 0) y con nombres en español
        $totalesPorMes = $ventasMeses->pluck('total', 'mes_key');
        $chartLabels = [];
        $chartData = [];

        for ($i = 5; $i >= 0; $i--) {
            $mes = Carbon::now()->startOfMonth()->subMonths($i);
            $chartLabels[] = ucfirst($mes->translatedFormat('M Y'));
            $chartData[] = (float)($totalesPorMes[$mes->format('Y-m')] ?? 0);
        }

        // 5. Productos más vendidos
        $topProductosQuery = DetalleVenta::select('producto_id', DB::raw('SUM(cantidad) as total_qty'))
            ->with('producto');

        if ($sucursalId) {
            $topProductosQuery->whereHas('venta', fn($q) => $q->where('sucursal_id', $sucursalId));
        }

        $topProductos = $topProductosQuery->groupBy('producto_id')
            ->orderBy('total_qty', 'desc')
            ->limit(5)
            ->get();

        $prodLabels = $topProductos->map(fn($p) => $p->producto->nombre ?? 'Producto')->toArray();
        $prodData = $topProductos->map(fn($p) => (int)$p->total_qty)->toArray();

        return view('dashboard.index', compact(
            'config',
            'totalSocios',
            'ingresosMes',
            'gastosMes',
            'utilidadMes',
            'ventasRecientes',
            'bajoStock',
            'chartLabels',
            'chartData',
            'prodLabels',
            'prodData'
        ));
    }
}
