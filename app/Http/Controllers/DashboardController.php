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

        // 1. KPIs
        $totalSocios = Socio::where('estado', 'activo')->count();

        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();

        $ingresosMes = (float)Venta::whereBetween('fecha', [$inicioMes, $finMes])->sum('total');

        // Solo gastos no anulados
        $gastosMes = (float)Gasto::whereBetween('fecha', [$inicioMes->format('Y-m-d'), $finMes->format('Y-m-d')])
            ->where('estado', '!=', 'anulado')
            ->sum('monto');

        $utilidadMes = $ingresosMes - $gastosMes;

        // 2. Ventas recientes
        $ventasRecientes = Venta::with(['socio', 'caja.usuario'])
            ->orderBy('fecha', 'desc')
            ->limit(5)
            ->get();

        // 3. Alertas de inventario
        $bajoStock = Producto::where('stock', '<=', 10)
            ->where('estado', 'activo')
            ->orderBy('stock', 'asc')
            ->limit(5)
            ->get();

        // 4. Gráfico Ventas últimos 6 meses (Compatible con PostgreSQL y MySQL)
        $isPgsql = config('database.default') === 'pgsql';
        $mesKeyExpr = $isPgsql ? "TO_CHAR(fecha, 'YYYY-MM')" : "DATE_FORMAT(fecha, '%Y-%m')";
        $mesNomExpr = $isPgsql ? "TO_CHAR(fecha, 'Mon YYYY')" : "DATE_FORMAT(fecha, '%b %Y')";
        $intervalExpr = $isPgsql ? "NOW() - INTERVAL '6 months'" : "DATE_SUB(NOW(), INTERVAL 6 MONTH)";

        $ventasMeses = Venta::selectRaw("{$mesKeyExpr} as mes_key, {$mesNomExpr} as mes_nombre, SUM(total) as total")
            ->whereRaw("fecha >= {$intervalExpr}")
            ->groupByRaw("mes_key, mes_nombre")
            ->orderByRaw("mes_key ASC")
            ->get();

        $chartLabels = $ventasMeses->pluck('mes_nombre')->toArray();
        $chartData = $ventasMeses->pluck('total')->map(fn($v) => (float)$v)->toArray();

        // 5. Productos más vendidos
        $topProductos = DetalleVenta::select('producto_id', DB::raw('SUM(cantidad) as total_qty'))
            ->with('producto')
            ->groupBy('producto_id')
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
