@extends('layouts.app')

@section('title', 'Control de Gastos')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
        <div>
            <h3 class="fw-extrabold text-dark mb-1">Gastos y Salidas de Caja</h3>
            <p class="text-muted small mb-0">Control de pagos operativos, compras menores, servicios y mantenimiento</p>
        </div>
        <a href="{{ route('gastos.crear') }}" class="btn btn-danger rounded-pill px-4 py-2 fw-bold d-flex align-items-center gap-2 shadow-sm">
            <i class="fas fa-minus-circle"></i> <span>Registrar Nuevo Gasto</span>
        </a>
    </div>

    <!-- Tabla de Gastos -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle table-data mb-0">
                    <thead class="table-light">
                        <tr class="text-muted small text-uppercase">
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Descripción</th>
                            <th>Registrado Por</th>
                            <th>Turno Caja</th>
                            <th>Monto</th>
                            <th>Estado</th>
                            <th class="text-end">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($gastos as $g)
                            <tr class="{{ $g->estado === 'anulado' ? 'opacity-50 text-decoration-line-through' : '' }}">
                                <td class="fw-bold">#{{ $g->id }}</td>
                                <td>{{ $g->fecha ? \Carbon\Carbon::parse($g->fecha)->format('d/m/Y') : '-' }}</td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $g->descripcion }}</div>
                                    @if($g->venta_id)
                                        <a href="{{ route('ticket.ver', $g->venta_id) }}" target="_blank" class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 text-decoration-none mt-1" title="Ver Ticket Asociado">
                                            <i class="fas fa-receipt me-1"></i> Ver Ticket #{{ str_pad($g->venta_id, 5, '0', STR_PAD_LEFT) }}
                                        </a>
                                    @endif
                                    @if($g->estado === 'anulado' && $g->motivo_anulacion)
                                        <small class="text-danger d-block">Motivo anulación: {{ $g->motivo_anulacion }}</small>
                                    @endif
                                </td>
                                <td>{{ $g->usuario->nombre ?? 'N/A' }}</td>
                                <td>
                                    @if($g->caja_id)
                                        <span class="badge bg-light text-dark border">Caja #{{ $g->caja_id }}</span>
                                    @else
                                        <span class="text-muted small">Fuera de caja</span>
                                    @endif
                                </td>
                                <td class="fw-bold text-danger fs-6">
                                    - {{ $config->moneda ?? 'S/' }} {{ number_format($g->monto, 2) }}
                                </td>
                                <td>
                                    @if($g->estado === 'anulado')
                                        <span class="badge bg-secondary rounded-pill px-3 py-1">Anulado</span>
                                    @elseif($g->venta_id || $g->tipo === 'descuento_venta')
                                        <span class="badge bg-warning bg-opacity-10 text-warning text-dark border border-warning border-opacity-25 rounded-pill px-3 py-1 fw-bold">
                                            <i class="fas fa-tags me-1"></i> Descuento Venta
                                        </span>
                                    @else
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-bold">Salida Efectivo</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($g->venta_id)
                                        <a href="{{ route('ticket.ver', $g->venta_id) }}" target="_blank" class="btn btn-sm btn-light border text-primary rounded-pill px-3 fw-semibold">
                                            <i class="fas fa-receipt me-1"></i> Ticket
                                        </a>
                                    @elseif($g->estado !== 'anulado')
                                        <button class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="abrirModalAnular({{ $g->id }}, '{{ addslashes($g->descripcion) }}')">
                                            <i class="fas fa-ban me-1"></i> Anular
                                        </button>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Anular Gasto -->
<div class="modal fade" id="modalAnularGasto" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger">Anular Gasto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('gastos.anular') }}" method="POST">
                @csrf
                <input type="hidden" name="id" id="anular_id">

                <div class="modal-body p-4">
                    <p class="text-muted small">¿Está seguro de anular el gasto <strong id="anular_desc" class="text-dark"></strong>? El monto se revertirá del arqueo de caja.</p>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary mb-1">Motivo de Anulación <span class="text-danger">*</span></label>
                        <input type="text" name="motivo_anulacion" class="form-control rounded-3" placeholder="Ej: Error de digitación, duplicado..." required>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">Confirmar Anulación</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const modalAnular = new bootstrap.Modal(document.getElementById('modalAnularGasto'));

function abrirModalAnular(id, desc) {
    document.getElementById('anular_id').value = id;
    document.getElementById('anular_desc').textContent = desc;
    modalAnular.show();
}
</script>
@endsection
