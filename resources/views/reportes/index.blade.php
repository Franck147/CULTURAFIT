@extends('layouts.app')

@section('title', 'Reportes Ejecutivos & Auditoría')

@push('styles')
<style>
@media print {
    #sidebar-wrapper, .navbar, .btn, footer, .card-header ul.nav, form, .dataTables_filter, .dataTables_length, .dataTables_info, .dataTables_paginate {
        display: none !important;
    }
    #page-content-wrapper {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }
    body {
        background-color: #ffffff !important;
        color: #000000 !important;
        font-size: 11pt !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    .tab-content > .tab-pane {
        display: block !important;
        opacity: 1 !important;
        visibility: visible !important;
    }
    table {
        width: 100% !important;
        border-collapse: collapse !important;
        font-size: 9pt !important;
    }
    th, td {
        border: 1px solid #ddd !important;
        padding: 6px !important;
    }
    .badge {
        border: 1px solid #333 !important;
        color: #000 !important;
        background: transparent !important;
    }
    .print-header {
        display: block !important;
        margin-bottom: 20px !important;
        border-bottom: 2px solid #000 !important;
        padding-bottom: 10px !important;
    }
}
.print-header {
    display: none;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Encabezado Imprimible (Solo visible al imprimir) -->
    <div class="print-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-0 fw-bold">{{ $config->nombre_sistema ?? 'CULTURA FIT' }}</h2>
                <div>RUC: {{ $config->ruc ?? '-' }} | Tel: {{ $config->telefono ?? '-' }}</div>
                <div>Dirección: {{ $config->direccion ?? '-' }}</div>
            </div>
            <div class="text-end">
                <h4 class="mb-0 fw-bold">REPORTE FINANCIERO Y AUDITORÍA</h4>
                <div>Período: <strong>{{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }}</strong> al <strong>{{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}</strong></div>
                <small class="text-muted">Fecha de emisión: {{ date('d/m/Y H:i:s') }}</small>
            </div>
        </div>
    </div>
    <!-- Header Principal -->
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
        <div>
            <h3 class="fw-extrabold text-dark mb-1">
                <i class="fas fa-chart-line text-primary me-2"></i>Reportes Ejecutivos & Auditoría Financiera
            </h3>
            <p class="text-muted small mb-0">
                Auditoría pormenorizada de ventas por cajero, arqueos, métodos de pago y balance operativo
            </p>
        </div>
        <div class="d-flex gap-2">
            <!-- Botón Exportar a Excel -->
            <a href="{{ route('reportes.exportar-excel', request()->query()) }}" class="btn btn-outline-success rounded-pill px-3 py-2 fw-bold d-flex align-items-center gap-2 shadow-xs" title="Descargar reporte en formato Excel (CSV)">
                <i class="fas fa-file-excel fs-6 text-success"></i> <span>Exportar a Excel</span>
            </a>
            <!-- Botón Imprimir -->
            <button onclick="window.print()" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-bold d-flex align-items-center gap-2">
                <i class="fas fa-print"></i> <span>Imprimir Balance</span>
            </button>
        </div>
    </div>

    <!-- Barra de Filtros Avanzada -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <!-- Presets Rápidos de Fecha -->
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3 pb-3 border-bottom">
                <span class="small fw-bold text-muted text-uppercase me-2"><i class="fas fa-calendar-check me-1"></i> Períodos:</span>
                <a href="{{ route('reportes.index', array_merge(request()->except('preset', 'desde', 'hasta'), ['preset' => 'hoy'])) }}" 
                   class="btn btn-sm rounded-pill px-3 {{ $preset === 'hoy' ? 'btn-primary' : 'btn-light border' }}">Hoy</a>
                <a href="{{ route('reportes.index', array_merge(request()->except('preset', 'desde', 'hasta'), ['preset' => 'ayer'])) }}" 
                   class="btn btn-sm rounded-pill px-3 {{ $preset === 'ayer' ? 'btn-primary' : 'btn-light border' }}">Ayer</a>
                <a href="{{ route('reportes.index', array_merge(request()->except('preset', 'desde', 'hasta'), ['preset' => 'semana'])) }}" 
                   class="btn btn-sm rounded-pill px-3 {{ $preset === 'semana' ? 'btn-primary' : 'btn-light border' }}">Esta Semana</a>
                <a href="{{ route('reportes.index', array_merge(request()->except('preset', 'desde', 'hasta'), ['preset' => 'mes'])) }}" 
                   class="btn btn-sm rounded-pill px-3 {{ ($preset === 'mes' || (!$preset && $desde === \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') && $hasta === \Carbon\Carbon::now()->format('Y-m-d'))) ? 'btn-primary' : 'btn-light border' }}">Este Mes</a>
                <a href="{{ route('reportes.index', array_merge(request()->except('preset', 'desde', 'hasta'), ['preset' => 'mes_anterior'])) }}" 
                   class="btn btn-sm rounded-pill px-3 {{ $preset === 'mes_anterior' ? 'btn-primary' : 'btn-light border' }}">Mes Anterior</a>
                <a href="{{ route('reportes.index', array_merge(request()->except('preset', 'desde', 'hasta'), ['preset' => 'anio'])) }}" 
                   class="btn btn-sm rounded-pill px-3 {{ $preset === 'anio' ? 'btn-primary' : 'btn-light border' }}">Año Actual</a>
            </div>

            <!-- Formulario de Filtros Específicos -->
            <form action="{{ route('reportes.index') }}" method="GET" class="row g-3 align-items-end">
                <!-- Filtro por Sucursal -->
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label small fw-bold text-secondary mb-1">
                        <i class="fas fa-store text-muted me-1"></i> Sucursal / Local
                    </label>
                    <select name="sucursal_id" class="form-select rounded-3">
                        <option value="todas">🌐 Todas las Sucursales</option>
                        @foreach($sucursales as $s)
                            <option value="{{ $s->id }}" {{ (string)$sucursalId === (string)$s->id ? 'selected' : '' }}>
                                📍 {{ $s->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Fecha Desde -->
                <div class="col-12 col-sm-6 col-lg-2">
                    <label class="form-label small fw-bold text-secondary mb-1">
                        <i class="fas fa-calendar-alt text-muted me-1"></i> Desde
                    </label>
                    <input type="date" name="desde" class="form-control rounded-3" value="{{ $desde }}">
                </div>

                <!-- Fecha Hasta -->
                <div class="col-12 col-sm-6 col-lg-2">
                    <label class="form-label small fw-bold text-secondary mb-1">
                        <i class="fas fa-calendar-alt text-muted me-1"></i> Hasta
                    </label>
                    <input type="date" name="hasta" class="form-control rounded-3" value="{{ $hasta }}">
                </div>

                <!-- Filtro por Cajero -->
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label small fw-bold text-secondary mb-1">
                        <i class="fas fa-user-tie text-muted me-1"></i> Cajero Responsable
                    </label>
                    <select name="cajero_id" class="form-select rounded-3">
                        <option value="">-- Todos los Cajeros --</option>
                        @foreach($cajeros as $caj)
                            <option value="{{ $caj->id }}" {{ (string)$cajeroId === (string)$caj->id ? 'selected' : '' }}>
                                {{ $caj->nombre }} ({{ ucfirst($caj->rol) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro por Método de Pago -->
                <div class="col-12 col-sm-6 col-lg-2">
                    <label class="form-label small fw-bold text-secondary mb-1">
                        <i class="fas fa-wallet text-muted me-1"></i> Método Pago
                    </label>
                    <select name="metodo_pago" class="form-select rounded-3">
                        <option value="">-- Todos --</option>
                        <option value="efectivo" {{ $metodoPago === 'efectivo' ? 'selected' : '' }}>💵 Efectivo</option>
                        <option value="tarjeta" {{ $metodoPago === 'tarjeta' ? 'selected' : '' }}>💳 Tarjeta</option>
                        <option value="yape_plin" {{ $metodoPago === 'yape_plin' ? 'selected' : '' }}>📱 Yape/Plin</option>
                        <option value="transferencia" {{ $metodoPago === 'transferencia' ? 'selected' : '' }}>🏦 Transf.</option>
                    </select>
                </div>

                <!-- Botones de Acción -->
                <div class="col-12 d-flex justify-content-end gap-2 mt-3 pt-2 border-top">
                    <a href="{{ route('reportes.index') }}" class="btn btn-light rounded-pill px-4" title="Reiniciar todos los filtros">
                        <i class="fas fa-undo me-1"></i> Limpiar
                    </a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                        <i class="fas fa-filter me-1"></i> Aplicar Filtros
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- KPIs Financieros Principales -->
    <div class="row g-3 mb-4">
        <!-- Facturación Neta -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden position-relative">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted fw-bold small text-uppercase">Ventas Netas</span>
                        <div class="rounded-3 bg-success bg-opacity-10 text-success p-2">
                            <i class="fas fa-cash-register fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fw-extrabold text-success mb-1">
                        {{ $config->moneda ?? 'S/' }} {{ number_format($totalVentasNetas, 2) }}
                    </h3>
                    <div class="d-flex justify-content-between align-items-center text-muted small">
                        <span>{{ number_format($cantVentas) }} tickets emitidos</span>
                        @if($totalDescuentos > 0)
                            <span class="text-danger" title="Total en descuentos">-{{ $config->moneda ?? 'S/' }}{{ number_format($totalDescuentos, 2) }}</span>
                        @endif
                    </div>
                </div>
                <div class="position-absolute bottom-0 start-0 end-0 bg-success" style="height: 4px;"></div>
            </div>
        </div>

        <!-- Egresos / Gastos -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden position-relative">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted fw-bold small text-uppercase">Gastos Operativos</span>
                        <div class="rounded-3 bg-danger bg-opacity-10 text-danger p-2">
                            <i class="fas fa-receipt fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fw-extrabold text-danger mb-1">
                        {{ $config->moneda ?? 'S/' }} {{ number_format($totalGastos, 2) }}
                    </h3>
                    <div class="d-flex justify-content-between align-items-center text-muted small">
                        <span>{{ count($gastos) }} egresos registrados</span>
                        <span class="badge bg-danger bg-opacity-10 text-danger">Salidas</span>
                    </div>
                </div>
                <div class="position-absolute bottom-0 start-0 end-0 bg-danger" style="height: 4px;"></div>
            </div>
        </div>

        <!-- Utilidad Neta -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden position-relative">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted fw-bold small text-uppercase">Utilidad Neta</span>
                        <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2">
                            <i class="fas fa-chart-pie fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fw-extrabold {{ $utilidadNeta >= 0 ? 'text-primary' : 'text-danger' }} mb-1">
                        {{ $config->moneda ?? 'S/' }} {{ number_format($utilidadNeta, 2) }}
                    </h3>
                    <div class="d-flex align-items-center gap-1 small {{ $utilidadNeta >= 0 ? 'text-success' : 'text-danger' }}">
                        <i class="fas {{ $utilidadNeta >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i>
                        <span>Margen: {{ number_format($margenRentabilidad, 1) }}% de ventas</span>
                    </div>
                </div>
                <div class="position-absolute bottom-0 start-0 end-0 bg-primary" style="height: 4px;"></div>
            </div>
        </div>

        <!-- Ticket Promedio & Métricas -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden position-relative">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted fw-bold small text-uppercase">Ticket Promedio</span>
                        <div class="rounded-3 bg-info bg-opacity-10 text-info p-2">
                            <i class="fas fa-tags fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fw-extrabold text-dark mb-1">
                        {{ $config->moneda ?? 'S/' }} {{ number_format($ticketPromedio, 2) }}
                    </h3>
                    <div class="d-flex justify-content-between align-items-center text-muted small">
                        <span>Gasto medio por orden</span>
                        <span class="badge bg-light text-dark border">{{ $sociosActivos }} socios act.</span>
                    </div>
                </div>
                <div class="position-absolute bottom-0 start-0 end-0 bg-info" style="height: 4px;"></div>
            </div>
        </div>
    </div>

    <!-- Pestañas de Auditoría y Desglose -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-transparent border-0 p-4 pb-0">
            <ul class="nav nav-pills gap-2" id="reportesTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-4 py-2 fw-bold active" id="ventas-tab" data-bs-toggle="pill" data-bs-target="#tabVentas" type="button" role="tab">
                        <i class="fas fa-list-check me-2"></i> Detalle de Ventas por Cajero
                        <span class="badge bg-primary rounded-pill ms-2">{{ $cantVentas }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-4 py-2 fw-bold" id="cajeros-tab" data-bs-toggle="pill" data-bs-target="#tabCajeros" type="button" role="tab">
                        <i class="fas fa-users-gear me-2"></i> Rendimiento por Cajero
                        <span class="badge bg-secondary rounded-pill ms-2">{{ count($rendimientoCajeros) }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-4 py-2 fw-bold" id="graficos-tab" data-bs-toggle="pill" data-bs-target="#tabGraficos" type="button" role="tab">
                        <i class="fas fa-chart-area me-2"></i> Gráficos & Tendencias
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-4 py-2 fw-bold" id="gastos-tab" data-bs-toggle="pill" data-bs-target="#tabGastos" type="button" role="tab">
                        <i class="fas fa-arrow-down-wide-short me-2"></i> Egresos Operativos
                        <span class="badge bg-danger rounded-pill ms-2">{{ count($gastos) }}</span>
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-4">
            <div class="tab-content" id="reportesTabContent">

                <!-- TAB 1: DETALLE DE VENTAS CON CAJERO -->
                <div class="tab-pane fade show active" id="tabVentas" role="tabpanel">
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
                        <div>
                            <h5 class="fw-bold mb-0">Registro Completo de Ventas</h5>
                            <small class="text-muted">Mostrando transacciones coincidentes con el período y filtros aplicados</small>
                        </div>
                        <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill">
                            Total Facturado: <strong>{{ $config->moneda ?? 'S/' }} {{ number_format($totalVentasNetas, 2) }}</strong>
                        </span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-data mb-0">
                            <thead class="table-light">
                                <tr class="text-muted small text-uppercase">
                                    <th># Ticket</th>
                                    <th>Fecha & Hora</th>
                                    <th>Cajero Responsable</th>
                                    <th>Cliente / Socio</th>
                                    <th>Método Pago</th>
                                    <th class="text-center">Artículos</th>
                                    <th>Subtotal</th>
                                    <th>Descuento</th>
                                    <th class="text-end">Total Cobrado</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ventas as $v)
                                    @php
                                        $cajero = $v->caja && $v->caja->usuario ? $v->caja->usuario : null;
                                        $articulosQty = $v->detalles->sum('cantidad');
                                    @endphp
                                    <tr>
                                        <!-- Ticket -->
                                        <td>
                                            <a href="{{ route('ticket.ver', $v->id) }}" target="_blank" class="fw-bold text-primary text-decoration-none">
                                                #{{ str_pad($v->id, 5, '0', STR_PAD_LEFT) }}
                                            </a>
                                        </td>

                                        <!-- Fecha y Hora -->
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $v->fecha->format('d/m/Y') }}</div>
                                            <small class="text-muted"><i class="far fa-clock me-1"></i>{{ $v->fecha->format('H:i:s') }}</small>
                                        </td>

                                        <!-- Cajero con Avatar y Rol -->
                                        <td>
                                            @if($cajero)
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                        {{ strtoupper(substr($cajero->nombre, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-dark small">{{ $cajero->nombre }}</div>
                                                        <span class="badge {{ $cajero->rol === 'admin' ? 'bg-danger bg-opacity-10 text-danger' : 'bg-primary bg-opacity-10 text-primary' }} text-uppercase" style="font-size: 0.65rem;">
                                                            {{ $cajero->rol }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="badge bg-light text-muted border">Venta Directa</span>
                                            @endif
                                        </td>

                                        <!-- Socio / Cliente -->
                                        <td>
                                            @if($v->socio)
                                                <div class="fw-semibold text-dark small">{{ $v->socio->nombre }}</div>
                                                <small class="text-muted">DNI: {{ $v->socio->dni }}</small>
                                            @else
                                                <span class="text-muted small fst-italic">Venta Directa / General</span>
                                            @endif
                                        </td>

                                        <!-- Método de Pago -->
                                        <td>
                                            <span class="badge bg-light text-dark border rounded-pill px-2 py-1 small">
                                                <i class="{{ $v->metodo_pago_icono }} me-1"></i>
                                                {{ $v->metodo_pago_nombre }}
                                            </span>
                                        </td>

                                        <!-- Cantidad Artículos -->
                                        <td class="text-center">
                                            <span class="badge bg-light text-secondary border px-2 py-1">
                                                {{ $articulosQty }} uds.
                                            </span>
                                        </td>

                                        <!-- Subtotal -->
                                        <td class="small text-muted">
                                            {{ $config->moneda ?? 'S/' }} {{ number_format($v->total + $v->descuento, 2) }}
                                        </td>

                                        <!-- Descuento -->
                                        <td class="small {{ $v->descuento > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                                            {{ $v->descuento > 0 ? '-' . ($config->moneda ?? 'S/') . ' ' . number_format($v->descuento, 2) : '-' }}
                                        </td>

                                        <!-- Total Pagado -->
                                        <td class="text-end fw-extrabold text-dark fs-6">
                                            {{ $config->moneda ?? 'S/' }} {{ number_format($v->total, 2) }}
                                        </td>

                                        <!-- Acciones -->
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end gap-1">
                                                <button type="button" class="btn btn-sm btn-light text-primary rounded-circle p-2" title="Ver Detalle de Ítems" onclick="abrirModalDetalleVenta({{ $v->id }})">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <a href="{{ route('ticket.ver', $v->id) }}" target="_blank" class="btn btn-sm btn-light text-secondary rounded-circle p-2" title="Imprimir Ticket">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 2: RENDIMIENTO POR CAJERO -->
                <div class="tab-pane fade" id="tabCajeros" role="tabpanel">
                    <div class="mb-3">
                        <h5 class="fw-bold mb-1">Auditoría de Rendimiento por Cajero</h5>
                        <p class="text-muted small mb-0">Total recaudado, desglose por forma de pago y cuota de participación por usuario</p>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr class="text-muted small text-uppercase">
                                    <th>Cajero</th>
                                    <th class="text-center">Tickets Vendidos</th>
                                    <th>Efectivo Cobrado</th>
                                    <th>Tarjeta (POS)</th>
                                    <th>Transferencia</th>
                                    <th>Ticket Promedio</th>
                                    <th class="text-end">Total Recaudado</th>
                                    <th style="width: 15%;">% Participación</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rendimientoCajeros as $rc)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-extrabold d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    {{ strtoupper(substr($rc['nombre'], 0, 1)) }}
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-bold text-dark">{{ $rc['nombre'] }}</h6>
                                                    <span class="badge {{ $rc['rol'] === 'admin' ? 'bg-danger bg-opacity-10 text-danger' : 'bg-primary bg-opacity-10 text-primary' }} text-uppercase" style="font-size: 0.65rem;">
                                                        {{ $rc['rol'] }}
                                                    </span>
                                                    <small class="text-muted ms-1">{{ $rc['email'] }}</small>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="text-center fw-bold text-dark">
                                            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fs-6">
                                                {{ $rc['cantidad_ventas'] }}
                                            </span>
                                        </td>

                                        <td class="text-success fw-semibold small">
                                            {{ $config->moneda ?? 'S/' }} {{ number_format($rc['efectivo'], 2) }}
                                        </td>

                                        <td class="text-primary fw-semibold small">
                                            {{ $config->moneda ?? 'S/' }} {{ number_format($rc['tarjeta'], 2) }}
                                        </td>

                                        <td class="text-info fw-semibold small">
                                            {{ $config->moneda ?? 'S/' }} {{ number_format($rc['transferencia'], 2) }}
                                        </td>

                                        <td class="text-dark small fw-semibold">
                                            {{ $config->moneda ?? 'S/' }} {{ number_format($rc['ticket_promedio'], 2) }}
                                        </td>

                                        <td class="text-end fw-extrabold fs-6 text-dark">
                                            {{ $config->moneda ?? 'S/' }} {{ number_format($rc['total_recaudado'], 2) }}
                                        </td>

                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress flex-grow-1" style="height: 8px;">
                                                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $rc['participacion_porc'] }}%"></div>
                                                </div>
                                                <span class="small fw-bold text-muted">{{ number_format($rc['participacion_porc'], 1) }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">
                                            No hay registros de ventas para los cajeros en este período.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 3: GRÁFICOS Y MÉTODOS DE PAGO -->
                <div class="tab-pane fade" id="tabGraficos" role="tabpanel">
                    <div class="row g-4">
                        <!-- Tendencia Diaria -->
                        <div class="col-12 col-xl-8">
                            <div class="border rounded-4 p-4 h-100">
                                <h6 class="fw-bold mb-1">Evolución de Ingresos en el Período</h6>
                                <span class="text-muted small d-block mb-3">Facturación diaria consolidada</span>
                                <div style="height: 290px; position: relative;">
                                    @if(count($chartLabels) > 0)
                                        <canvas id="chartTendenciaDiaria"></canvas>
                                    @else
                                        <div class="h-100 d-flex align-items-center justify-content-center text-muted">
                                            <small>Sin datos de ventas para graficar.</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Resumen Métodos de Pago -->
                        <div class="col-12 col-xl-4">
                            <div class="border rounded-4 p-4 h-100">
                                <h6 class="fw-bold mb-1">Distribución por Método</h6>
                                <span class="text-muted small d-block mb-3">Proporción de cobranza</span>
                                <div style="height: 190px; position: relative;" class="d-flex align-items-center justify-content-center">
                                    <canvas id="chartMetodosPago"></canvas>
                                </div>
                                <div class="mt-3">
                                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom small">
                                        <span class="d-flex align-items-center gap-2">
                                            <span class="badge bg-success rounded-circle p-1"> </span>
                                            <strong>Efectivo</strong>
                                        </span>
                                        <span class="fw-bold text-dark">{{ $config->moneda ?? 'S/' }} {{ number_format($metodosTotales['efectivo']['total'], 2) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom small">
                                        <span class="d-flex align-items-center gap-2">
                                            <span class="badge bg-primary rounded-circle p-1"> </span>
                                            <strong>Tarjeta (POS)</strong>
                                        </span>
                                        <span class="fw-bold text-dark">{{ $config->moneda ?? 'S/' }} {{ number_format($metodosTotales['tarjeta']['total'], 2) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center py-2 small">
                                        <span class="d-flex align-items-center gap-2">
                                            <span class="badge bg-info rounded-circle p-1"> </span>
                                            <strong>Transferencia / Yape</strong>
                                        </span>
                                        <span class="fw-bold text-dark">{{ $config->moneda ?? 'S/' }} {{ number_format($metodosTotales['transferencia']['total'], 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 4: GASTOS Y EGRESOS -->
                <div class="tab-pane fade" id="tabGastos" role="tabpanel">
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
                        <div>
                            <h5 class="fw-bold mb-0">Egresos Operativos del Período</h5>
                            <small class="text-muted">Gastos en insumos, pagos menores y mantenimiento</small>
                        </div>
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 rounded-pill fw-bold">
                            Total Egresos: - {{ $config->moneda ?? 'S/' }} {{ number_format($totalGastos, 2) }}
                        </span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr class="text-muted small text-uppercase">
                                    <th>ID</th>
                                    <th>Fecha</th>
                                    <th>Descripción</th>
                                    <th>Usuario que Registró</th>
                                    <th>Turno Caja</th>
                                    <th class="text-end">Monto del Gasto</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($gastos as $g)
                                    <tr>
                                        <td class="fw-bold">#{{ $g->id }}</td>
                                        <td>{{ \Carbon\Carbon::parse($g->fecha)->format('d/m/Y') }}</td>
                                        <td class="fw-semibold text-dark">{{ $g->descripcion }}</td>
                                        <td>{{ $g->usuario->nombre ?? 'N/A' }}</td>
                                        <td>
                                            @if($g->caja_id)
                                                <span class="badge bg-light text-dark border">Caja #{{ $g->caja_id }}</span>
                                            @else
                                                <span class="text-muted small">Fuera de turno</span>
                                            @endif
                                        </td>
                                        <td class="text-end fw-extrabold text-danger fs-6">
                                            - {{ $config->moneda ?? 'S/' }} {{ number_format($g->monto, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            No se registraron egresos en el período seleccionado.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal Detalle Ítems de Venta -->
<div class="modal fade" id="modalDetalleVentaReporte" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalRepVentaTitle">Comprobante de Venta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="modalRepVentaBody">
                <div class="text-center py-4">
                    <span class="spinner-border text-primary"></span>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
                <a href="#" id="modalRepPrintLink" target="_blank" class="btn btn-primary rounded-pill px-4">
                    <i class="fas fa-print me-1"></i> Imprimir Ticket
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const moneda = '{{ $config->moneda ?? "S/" }}';

    // 1. Gráfico Diario de Ventas
    const ctxDiario = document.getElementById('chartTendenciaDiaria');
    if (ctxDiario && {!! count($chartLabels) !!} > 0) {
        new Chart(ctxDiario, {
            type: 'bar',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [{
                    label: 'Ventas (' + moneda + ')',
                    data: {!! json_encode($chartData) !!},
                    backgroundColor: 'rgba(99, 102, 241, 0.85)',
                    hoverBackgroundColor: '#4F46E5',
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Total: ' + moneda + ' ' + context.parsed.y.toLocaleString('es-PE', { minimumFractionDigits: 2 });
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#F1F5F9' },
                        ticks: {
                            callback: function(val) {
                                return moneda + ' ' + val.toLocaleString();
                            }
                        }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // 2. Gráfico Métodos de Pago
    const ctxMetodos = document.getElementById('chartMetodosPago');
    if (ctxMetodos) {
        new Chart(ctxMetodos, {
            type: 'doughnut',
            data: {
                labels: ['Efectivo', 'Tarjeta (POS)', 'Transferencia / Yape'],
                datasets: [{
                    data: [
                        {{ $metodosTotales['efectivo']['total'] }},
                        {{ $metodosTotales['tarjeta']['total'] }},
                        {{ $metodosTotales['transferencia']['total'] }}
                    ],
                    backgroundColor: ['#10B981', '#6366F1', '#06B6D4'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                cutout: '68%'
            }
        });
    }
});

// Modal AJAX para ver ítems de la venta seleccionada
function abrirModalDetalleVenta(id) {
    const modal = new bootstrap.Modal(document.getElementById('modalDetalleVentaReporte'));
    const body = document.getElementById('modalRepVentaBody');
    const title = document.getElementById('modalRepVentaTitle');
    const printLink = document.getElementById('modalRepPrintLink');

    title.textContent = `Venta #${id.toString().padStart(5, '0')}`;
    printLink.href = `/ticket/ver/${id}`;
    body.innerHTML = '<div class="text-center py-4"><span class="spinner-border text-primary"></span></div>';
    modal.show();

    fetch(`/pos/detalle/${id}`)
        .then(res => res.json())
        .then(data => {
            const moneda = '{{ $config->moneda ?? "S/" }}';
            let metodoTexto = data.metodo_pago;
            if (['yape_plin', 'yape', 'plin'].includes(String(data.metodo_pago).toLowerCase())) {
                metodoTexto = 'Yape / Plin';
            } else if (data.metodo_pago) {
                metodoTexto = data.metodo_pago.charAt(0).toUpperCase() + data.metodo_pago.slice(1);
            }

            let html = `
                <div class="p-3 bg-light rounded-3 mb-3 small">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Fecha y Hora:</span>
                        <strong class="text-dark">${new Date(data.fecha).toLocaleString()}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Cajero Responsable:</span>
                        <strong class="text-primary">${data.caja && data.caja.usuario ? data.caja.usuario.nombre : 'N/A'}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Cliente / Socio:</span>
                        <strong class="text-dark">${data.socio ? data.socio.nombre : 'Venta Directa'}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Forma de Pago:</span>
                        <strong class="text-dark">${metodoTexto}</strong>
                    </div>
                </div>

                <h6 class="fw-bold small text-uppercase text-secondary mb-2">Artículos del Comprobante</h6>
                <div class="list-group list-group-flush mb-3">
            `;

            data.detalles.forEach(d => {
                html += `
                    <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold small text-dark">${d.producto ? d.producto.nombre : 'Producto'}</div>
                            <small class="text-muted">${d.cantidad} x ${moneda} ${parseFloat(d.precio_unitario).toFixed(2)}</small>
                        </div>
                        <span class="fw-bold text-dark small">${moneda} ${parseFloat(d.subtotal).toFixed(2)}</span>
                    </div>
                `;
            });

            html += `
                </div>
                <div class="border-top pt-2">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>Subtotal:</span>
                        <span>${moneda} ${(parseFloat(data.total) + parseFloat(data.descuento || 0)).toFixed(2)}</span>
                    </div>
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>Descuento:</span>
                        <span>- ${moneda} ${parseFloat(data.descuento || 0).toFixed(2)}</span>
                    </div>
                    <div class="d-flex justify-content-between fw-bold fs-5 text-dark mt-2">
                        <span>Total Pagado:</span>
                        <span class="text-success">${moneda} ${parseFloat(data.total).toFixed(2)}</span>
                    </div>
                </div>
            `;

            body.innerHTML = html;
        })
        .catch(err => {
            body.innerHTML = '<div class="alert alert-danger mb-0">Error al consultar el detalle de la venta.</div>';
        });
}
</script>
@endpush
