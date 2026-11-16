@extends('layouts.app')

@section('title', 'Cierre de Caja')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <!-- Header -->
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
                <div>
                    <h3 class="fw-extrabold text-dark mb-1">Cierre y Arqueo de Turno #{{ $cajaAbierta->id }}</h3>
                    <p class="text-muted small mb-0">
                        Iniciado por <strong>{{ $cajaAbierta->usuario->nombre ?? 'Usuario' }}</strong> el {{ $cajaAbierta->fecha_apertura->format('d/m/Y \a \l\a\s H:i') }}
                    </p>
                </div>
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill fs-6 fw-bold">
                    <i class="fas fa-dot-circle me-1 animate-pulse"></i> Turno Activo
                </span>
            </div>

            <div class="row g-4">
                <!-- Resumen Financiero del Turno -->
                <div class="col-12 col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-transparent border-0 p-4 pb-0">
                            <h5 class="fw-bold mb-1">Desglose de Movimientos del Turno</h5>
                            <span class="text-muted small">Cálculo de ingresos y egresos registrados</span>
                        </div>
                        <div class="card-body p-4">
                            <div class="list-group list-group-flush rounded-3">
                                <!-- Fondo Inicial -->
                                <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-2">
                                            <i class="fas fa-lock-open"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark">Fondo Inicial de Apertura</div>
                                            <small class="text-muted">Efectivo al abrir caja</small>
                                        </div>
                                    </div>
                                    <span class="fw-bold text-dark fs-6">{{ $config->moneda ?? 'S/' }} {{ number_format($totales['monto_inicial'] ?? $cajaAbierta->monto_inicial ?? 0, 2) }}</span>
                                </div>

                                <!-- Ventas en Efectivo -->
                                <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-success bg-opacity-10 text-success rounded-3 p-2">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark">(+) Ventas en Efectivo</div>
                                            <small class="text-muted">Cobrado en ventanilla</small>
                                        </div>
                                    </div>
                                    <span class="fw-bold text-success fs-6">+ {{ $config->moneda ?? 'S/' }} {{ number_format($totales['ventas_efectivo'] ?? 0, 2) }}</span>
                                </div>

                                <!-- Gastos / Salidas en Efectivo -->
                                <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-danger bg-opacity-10 text-danger rounded-3 p-2">
                                            <i class="fas fa-arrow-down"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark">(-) Gastos y Salidas en Efectivo</div>
                                            <small class="text-muted">Pagos operativos durante el turno</small>
                                        </div>
                                    </div>
                                    <span class="fw-bold text-danger fs-6">- {{ $config->moneda ?? 'S/' }} {{ number_format($totales['gastos'] ?? $totales['total_gastos'] ?? 0, 2) }}</span>
                                </div>

                                <!-- Ventas Digitales: Tarjeta -->
                                <div class="list-group-item d-flex justify-content-between align-items-center py-2 bg-light bg-opacity-50">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas fa-credit-card text-primary small"></i>
                                        <span class="small text-muted">Ventas con Tarjeta (POS):</span>
                                    </div>
                                    <span class="fw-semibold text-dark small">{{ $config->moneda ?? 'S/' }} {{ number_format($totales['ventas_tarjeta'] ?? 0, 2) }}</span>
                                </div>

                                <!-- Ventas Digitales: Yape / Plin -->
                                <div class="list-group-item d-flex justify-content-between align-items-center py-2 bg-light bg-opacity-50">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas fa-qrcode small" style="color: #8B5CF6;"></i>
                                        <span class="small text-muted">Ventas con Yape / Plin:</span>
                                    </div>
                                    <span class="fw-semibold text-dark small">{{ $config->moneda ?? 'S/' }} {{ number_format($totales['ventas_yape_plin'] ?? 0, 2) }}</span>
                                </div>

                                <!-- Ventas Digitales: Transferencia -->
                                <div class="list-group-item d-flex justify-content-between align-items-center py-2 bg-light bg-opacity-50">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas fa-university text-info small"></i>
                                        <span class="small text-muted">Ventas con Transferencia:</span>
                                    </div>
                                    <span class="fw-semibold text-dark small">{{ $config->moneda ?? 'S/' }} {{ number_format($totales['ventas_transferencia'] ?? 0, 2) }}</span>
                                </div>
                            </div>

                            <!-- Esperado Físico -->
                            <div class="p-3 mt-4 rounded-4 bg-primary bg-opacity-10 border border-primary border-opacity-25 d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-primary fw-bold text-uppercase small d-block">Efectivo Físico Esperado en Cajón:</span>
                                    <small class="text-muted">(Inicial + Ventas Efectivo - Gastos)</small>
                                </div>
                                <span class="fs-4 fw-extrabold text-primary" id="montoEsperadoSpan" data-esperado="{{ $totales['esperado_efectivo'] ?? $totales['saldo_esperado'] ?? 0 }}">
                                    {{ $config->moneda ?? 'S/' }} {{ number_format($totales['esperado_efectivo'] ?? $totales['saldo_esperado'] ?? 0, 2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulario de Conteo Físico y Cierre -->
                <div class="col-12 col-lg-5">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-transparent border-0 p-4 pb-0">
                            <h5 class="fw-bold mb-1">Arqueo y Cierre Final</h5>
                            <span class="text-muted small">Cuente el efectivo físico en gaveta</span>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('caja.cerrar') }}" method="POST" id="formCerrarCaja">
                                @csrf
                                <input type="hidden" name="caja_id" value="{{ $cajaAbierta->id }}">

                                <div class="mb-4">
                                    <label class="form-label fw-bold text-dark mb-1">Efectivo Físico Real Contado</label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-light border-end-0 fw-bold text-muted">{{ $config->moneda ?? 'S/' }}</span>
                                        <input type="number" step="0.01" min="0" name="monto_fisico" id="monto_fisico" class="form-control border-start-0 fs-3 fw-bold text-dark" placeholder="0.00" required autofocus>
                                    </div>
                                    <small class="text-muted">Ingrese la suma de todos los billetes y monedas físicos en la gaveta.</small>
                                </div>

                                <!-- Cuadro de Diferencia Dinámica -->
                                <div id="boxDiferencia" class="p-3 rounded-4 mb-4 text-center d-none">
                                    <span class="small fw-bold text-uppercase d-block" id="diffLabel">Diferencia de Caja</span>
                                    <h3 class="fw-extrabold mb-1" id="diffValue">0.00</h3>
                                    <p class="small mb-0" id="diffDesc"></p>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-danger py-3 rounded-pill fw-bold fs-6 shadow-sm d-flex align-items-center justify-content-center gap-2" id="btnCerrarTurno">
                                        <i class="fas fa-lock"></i> Finalizar y Cerrar Turno
                                    </button>
                                    <a href="{{ route('pos.index') }}" class="btn btn-light rounded-pill py-2 text-muted fw-semibold">
                                        Continuar Vendiendo
                                    </a>
                                </div>
                            </form>
                        </div>
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
    const esperado = parseFloat(document.getElementById('montoEsperadoSpan').dataset.esperado) || 0;
    const inputFisico = document.getElementById('monto_fisico');
    const boxDiff = document.getElementById('boxDiferencia');
    const diffValue = document.getElementById('diffValue');
    const diffDesc = document.getElementById('diffDesc');
    const btnCerrar = document.getElementById('btnCerrarTurno');
    const form = document.getElementById('formCerrarCaja');

    inputFisico.addEventListener('input', function () {
        const valor = parseFloat(this.value);
        if (isNaN(valor)) {
            boxDiff.classList.add('d-none');
            return;
        }

        boxDiff.classList.remove('d-none');
        const diff = valor - esperado;
        const moneda = '{{ $config->moneda ?? "S/" }}';

        if (Math.abs(diff) < 0.01) {
            boxDiff.className = 'p-3 rounded-4 mb-4 text-center bg-success bg-opacity-10 border border-success text-success';
            diffValue.textContent = 'Cuadre Perfecto (0.00)';
            diffDesc.textContent = 'El dinero físico coincide exactamente con lo esperado.';
        } else if (diff > 0) {
            boxDiff.className = 'p-3 rounded-4 mb-4 text-center bg-info bg-opacity-10 border border-info text-info';
            diffValue.textContent = '+ ' + moneda + ' ' + diff.toFixed(2);
            diffDesc.textContent = 'Hay un sobrante de efectivo en gaveta.';
        } else {
            boxDiff.className = 'p-3 rounded-4 mb-4 text-center bg-danger bg-opacity-10 border border-danger text-danger';
            diffValue.textContent = '- ' + moneda + ' ' + Math.abs(diff).toFixed(2);
            diffDesc.textContent = 'Alerta: Hay un faltante de dinero físico en gaveta.';
        }
    });

    btnCerrar.addEventListener('click', function () {
        const valor = parseFloat(inputFisico.value);
        if (isNaN(valor) || valor < 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Monto Físico Inválido',
                text: 'Por favor ingrese el total físico contado en gaveta.'
            });
            return;
        }

        const diff = valor - esperado;
        let advertencia = '';
        if (Math.abs(diff) >= 0.01) {
            advertencia = ` Hay una diferencia de ${diff > 0 ? '+' : ''}${diff.toFixed(2)}.`;
        }

        Swal.fire({
            title: '¿Confirmar cierre de caja?',
            text: 'Se cerrará el turno operativo actual.' + advertencia,
            icon: Math.abs(diff) >= 0.01 ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Sí, cerrar turno',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>
@endpush
