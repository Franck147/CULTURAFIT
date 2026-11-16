@extends('layouts.app')

@section('title', 'Apertura de Caja')

@section('content')
<div class="container-fluid">
    <div class="row g-4">
        <!-- Formulario de Apertura -->
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 p-md-5">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="rounded-4 bg-primary bg-opacity-10 text-primary p-3">
                            <i class="fas fa-cash-register fs-2"></i>
                        </div>
                        <div>
                            <h4 class="fw-extrabold text-dark mb-1">Apertura de Turno</h4>
                            <span class="text-muted small">Inicie un nuevo turno para habilitar ventas</span>
                        </div>
                    </div>

                    <div class="alert alert-info border-0 rounded-4 p-3 mb-4 small">
                        <i class="fas fa-info-circle me-1"></i>
                        No tiene ninguna caja abierta actualmente. Ingrese el fondo de cambio (monto inicial en efectivo) para comenzar.
                    </div>

                    <form action="{{ route('caja.abrir') }}" method="POST">
                        @csrf

                        <!-- Selección o Indicador de Sucursal -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark mb-1">Sucursal / Local de Venta</label>
                            @if(auth()->user()->sucursal)
                                <div class="p-3 bg-light rounded-4 border d-flex align-items-center justify-content-between">
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold">Asignada</span>
                                            <strong class="text-dark">{{ auth()->user()->sucursal->nombre }}</strong>
                                        </div>
                                        <div class="text-muted small"><i class="fas fa-map-marker-alt text-danger me-1"></i>{{ auth()->user()->sucursal->direccion ?? 'Sin dirección' }}</div>
                                    </div>
                                    <div class="p-2 bg-white rounded-3 shadow-xs text-primary">
                                        <i class="fas fa-store fs-4"></i>
                                    </div>
                                </div>
                                <input type="hidden" name="sucursal_id" value="{{ auth()->user()->sucursal_id }}">
                            @else
                                <select name="sucursal_id" class="form-select form-select-lg rounded-3 py-2 fw-semibold border" required>
                                    @foreach($sucursales as $suc)
                                        <option value="{{ $suc->id }}" {{ $suc->es_principal ? 'selected' : '' }}>
                                            📍 {{ $suc->nombre }} ({{ $suc->codigo }}) {{ $suc->es_principal ? '— ⭐ Principal' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Seleccione la sucursal física donde iniciará este turno de caja.</small>
                            @endif
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark mb-1">Fondo Inicial en Caja (Efectivo)</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light border-end-0 fw-bold text-muted">{{ $config->moneda ?? 'S/' }}</span>
                                <input type="number" step="0.01" min="0" name="monto_inicial" id="monto_inicial" class="form-control border-start-0 fs-4 fw-bold text-primary" placeholder="0.00" value="{{ old('monto_inicial', '0.00') }}" required autofocus>
                            </div>
                            <small class="text-muted">Monto físico disponible para dar vuelto/cambio.</small>
                            @error('monto_inicial')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="p-3 bg-light rounded-4 mb-4">
                            <div class="d-flex justify-content-between mb-2 small">
                                <span class="text-muted">Cajero Responsable:</span>
                                <strong class="text-dark">{{ auth()->user()->nombre }}</strong>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span class="text-muted">Fecha y Hora de Apertura:</span>
                                <strong class="text-dark">{{ date('d/m/Y H:i:s') }}</strong>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold fs-6 shadow-sm d-flex align-items-center justify-content-center gap-2">
                            <i class="fas fa-lock-open"></i> Abrir Turno de Caja
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Historial Reciente de Cajas -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 p-4 pb-0 d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="fw-bold mb-1">Historial de Turnos Anteriores</h5>
                        <span class="text-muted small">Últimos turnos cerrados en el sistema</span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-data mb-0">
                            <thead class="table-light">
                                <tr class="text-muted small text-uppercase">
                                    <th># Turno</th>
                                    <th>Sucursal</th>
                                    <th>Cajero</th>
                                    <th>Apertura / Cierre</th>
                                    <th>Inicial</th>
                                    <th>Cierre Físico</th>
                                    <th>Diferencia</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($historial as $c)
                                    <tr>
                                        <td class="fw-bold">#{{ $c->id }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <i class="fas fa-store text-primary me-1"></i> {{ $c->sucursal->nombre ?? 'Sede Principal' }}
                                            </span>
                                        </td>
                                        <td>{{ $c->usuario->nombre ?? 'N/A' }}</td>
                                        <td>
                                            <div class="small fw-semibold">{{ $c->fecha_apertura ? $c->fecha_apertura->format('d/m/Y H:i') : '-' }}</div>
                                            <div class="small text-muted">{{ $c->fecha_cierre ? $c->fecha_cierre->format('d/m/Y H:i') : 'Sin cerrar' }}</div>
                                        </td>
                                        <td class="fw-semibold">{{ $config->moneda ?? 'S/' }} {{ number_format($c->monto_inicial, 2) }}</td>
                                        <td class="fw-bold text-dark">
                                            {{ $c->monto_cierre !== null ? ($config->moneda ?? 'S/') . ' ' . number_format($c->monto_cierre, 2) : '-' }}
                                        </td>
                                        <td>
                                            @if($c->diferencia !== null)
                                                @if($c->diferencia == 0)
                                                    <span class="badge bg-success bg-opacity-10 text-success fw-bold px-2 py-1">Exacto (0.00)</span>
                                                @elseif($c->diferencia > 0)
                                                    <span class="badge bg-info bg-opacity-10 text-info fw-bold px-2 py-1">+{{ number_format($c->diferencia, 2) }}</span>
                                                @else
                                                    <span class="badge bg-danger bg-opacity-10 text-danger fw-bold px-2 py-1">{{ number_format($c->diferencia, 2) }}</span>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge rounded-pill {{ $c->estado === 'abierta' ? 'bg-success' : 'bg-secondary' }}">
                                                {{ ucfirst($c->estado) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
