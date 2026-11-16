@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
        <div>
            <h3 class="fw-extrabold text-dark mb-1">Panel de Control</h3>
            <p class="text-muted small mb-0">Bienvenido de nuevo, <strong>{{ auth()->user()->nombre }}</strong>. Resumen operativo de {{ date('F Y') }}.</p>
        </div>
        <div class="d-flex gap-2">
            @if(auth()->user()->esAdmin() || auth()->user()->esCajero())
                <a href="{{ route('pos.index') }}" class="btn btn-primary rounded-pill px-3 py-2 fw-bold d-flex align-items-center gap-2 shadow-sm">
                    <i class="fas fa-shopping-cart"></i> <span>Punto de Venta</span>
                </a>
            @endif
            <a href="{{ route('socios.crear') }}" class="btn btn-outline-primary rounded-pill px-3 py-2 fw-bold d-flex align-items-center gap-2">
                <i class="fas fa-user-plus"></i> <span>Nuevo Socio</span>
            </a>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row g-3 mb-4">
        <!-- Socios Activos -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden position-relative">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-muted fw-bold small text-uppercase">Socios Activos</span>
                        <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2">
                            <i class="fas fa-users fs-5"></i>
                        </div>
                    </div>
                    <h2 class="fw-extrabold mb-1 text-dark">{{ number_format($totalSocios) }}</h2>
                    <div class="d-flex align-items-center gap-1 small text-success">
                        <i class="fas fa-check-circle"></i>
                        <span>En membresía vigente</span>
                    </div>
                </div>
                <div class="position-absolute bottom-0 start-0 end-0 bg-primary" style="height: 4px;"></div>
            </div>
        </div>

        <!-- Ingresos del Mes -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden position-relative">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-muted fw-bold small text-uppercase">Ingresos Mes</span>
                        <div class="rounded-3 bg-success bg-opacity-10 text-success p-2">
                            <i class="fas fa-arrow-down-left fs-5"></i>
                        </div>
                    </div>
                    <h2 class="fw-extrabold mb-1 text-success">{{ $config->moneda ?? 'S/' }} {{ number_format($ingresosMes, 2) }}</h2>
                    <div class="d-flex align-items-center gap-1 small text-muted">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Total facturado en POS</span>
                    </div>
                </div>
                <div class="position-absolute bottom-0 start-0 end-0 bg-success" style="height: 4px;"></div>
            </div>
        </div>

        <!-- Gastos del Mes -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden position-relative">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-muted fw-bold small text-uppercase">Gastos Mes</span>
                        <div class="rounded-3 bg-danger bg-opacity-10 text-danger p-2">
                            <i class="fas fa-arrow-up-right fs-5"></i>
                        </div>
                    </div>
                    <h2 class="fw-extrabold mb-1 text-danger">{{ $config->moneda ?? 'S/' }} {{ number_format($gastosMes, 2) }}</h2>
                    <div class="d-flex align-items-center gap-1 small text-muted">
                        <i class="fas fa-receipt"></i>
                        <span>Salidas y servicios</span>
                    </div>
                </div>
                <div class="position-absolute bottom-0 start-0 end-0 bg-danger" style="height: 4px;"></div>
            </div>
        </div>

        <!-- Utilidad Neta -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden position-relative">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-muted fw-bold small text-uppercase">Utilidad Neta</span>
                        <div class="rounded-3 bg-info bg-opacity-10 text-info p-2">
                            <i class="fas fa-wallet fs-5"></i>
                        </div>
                    </div>
                    <h2 class="fw-extrabold mb-1 {{ $utilidadMes >= 0 ? 'text-dark' : 'text-danger' }}">
                        {{ $config->moneda ?? 'S/' }} {{ number_format($utilidadMes, 2) }}
                    </h2>
                    <div class="d-flex align-items-center gap-1 small {{ $utilidadMes >= 0 ? 'text-success' : 'text-danger' }}">
                        <i class="fas {{ $utilidadMes >= 0 ? 'fa-chart-line' : 'fa-exclamation-triangle' }}"></i>
                        <span>Balance mensual</span>
                    </div>
                </div>
                <div class="position-absolute bottom-0 start-0 end-0 bg-info" style="height: 4px;"></div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-3 mb-4">
        <!-- Gráfico Ventas Semestrales -->
        <div class="col-12 col-xl-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h5 class="fw-bold mb-1">Tendencia de Ingresos</h5>
                            <span class="text-muted small">Evolución de ventas de los últimos 6 meses</span>
                        </div>
                        <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill">
                            <i class="fas fa-chart-area text-primary me-1"></i> Facturación
                        </span>
                    </div>
                    <div style="height: 280px; position: relative;">
                        <canvas id="salesTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico Productos Más Vendidos -->
        <div class="col-12 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h5 class="fw-bold mb-1">Top Productos</h5>
                            <span class="text-muted small">Artículos más demandados</span>
                        </div>
                        <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill">
                            <i class="fas fa-fire text-danger me-1"></i> Ranking
                        </span>
                    </div>
                    <div style="height: 280px; position: relative;" class="d-flex align-items-center justify-content-center">
                        @if(count($prodLabels) > 0)
                            <canvas id="topProductsChart"></canvas>
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-box-open fa-3x mb-2 opacity-50"></i>
                                <p class="small mb-0">Sin datos de ventas aún.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tables Row: Ventas Recientes & Stock Bajo -->
    <div class="row g-3">
        <!-- Ventas Recientes -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 p-4 pb-0 d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="fw-bold mb-1">Últimas Ventas Realizadas</h5>
                        <span class="text-muted small">Movimientos recientes en el punto de venta</span>
                    </div>
                    <a href="{{ route('pos.historial') }}" class="btn btn-sm btn-light rounded-pill px-3">
                        Ver todas <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body p-4 pt-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr class="text-muted small text-uppercase">
                                    <th># Ticket</th>
                                    <th>Cliente</th>
                                    <th>Método</th>
                                    <th>Total</th>
                                    <th class="text-end">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ventasRecientes as $v)
                                    <tr>
                                        <td class="fw-bold text-primary">#{{ str_pad($v->id, 5, '0', STR_PAD_LEFT) }}</td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $v->socio->nombre ?? 'Venta Directa' }}</div>
                                            <small class="text-muted">{{ $v->fecha->format('d/m/Y H:i') }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border rounded-pill px-2 py-1 small">
                                                <i class="{{ $v->metodo_pago_icono }} me-1"></i>
                                                {{ $v->metodo_pago_nombre }}
                                            </span>
                                        </td>
                                        <td class="fw-bold text-dark">{{ $config->moneda ?? 'S/' }} {{ number_format($v->total, 2) }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('ticket.ver', $v->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill" title="Imprimir Ticket">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <i class="fas fa-receipt fa-2x mb-2 opacity-50"></i>
                                            <p class="small mb-0">No hay ventas registradas recientemente.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alertas de Bajo Stock -->
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 p-4 pb-0 d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="fw-bold mb-1">Alertas de Inventario</h5>
                        <span class="text-muted small">Artículos con stock crítico (≤ 10 uds.)</span>
                    </div>
                    @if(auth()->user()->esAdmin())
                        <a href="{{ route('inventario.productos') }}" class="btn btn-sm btn-light rounded-pill px-3">
                            Inventario <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    @endif
                </div>
                <div class="card-body p-4 pt-3">
                    <div class="list-group list-group-flush">
                        @forelse($bajoStock as $prod)
                            <div class="list-group-item px-0 py-3 d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-danger bg-opacity-10 text-danger rounded-3 p-2">
                                        <i class="fas fa-box"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-semibold text-dark">{{ $prod->nombre }}</h6>
                                        <small class="text-muted">Cód: {{ $prod->codigo ?? 'S/C' }}</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="badge {{ $prod->stock == 0 ? 'bg-danger' : 'bg-warning text-dark' }} rounded-pill px-3 py-1 fw-bold">
                                        {{ $prod->stock }} uds.
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-check-double fa-2x mb-2 text-success opacity-75"></i>
                                <p class="small mb-0">¡Todo en orden! No hay productos con stock crítico.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Gráfico de Tendencia
    const ctxTrend = document.getElementById('salesTrendChart');
    if (ctxTrend) {
        new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [{
                    label: 'Ventas ({{ $config->moneda ?? "S/" }})',
                    data: {!! json_encode($chartData) !!},
                    borderColor: '#6366F1',
                    backgroundColor: 'rgba(99, 102, 241, 0.08)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#6366F1',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#F1F5F9' },
                        ticks: {
                            callback: function(val) {
                                return '{{ $config->moneda ?? "S/" }} ' + val.toLocaleString();
                            }
                        }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // 2. Gráfico de Top Productos
    const ctxProd = document.getElementById('topProductsChart');
    if (ctxProd && {!! count($prodLabels) !!} > 0) {
        new Chart(ctxProd, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($prodLabels) !!},
                datasets: [{
                    data: {!! json_encode($prodData) !!},
                    backgroundColor: [
                        '#6366F1',
                        '#10B981',
                        '#F59E0B',
                        '#EC4899',
                        '#3B82F6'
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, font: { size: 11 } }
                    }
                },
                cutout: '65%'
            }
        });
    }
});
</script>
@endpush
