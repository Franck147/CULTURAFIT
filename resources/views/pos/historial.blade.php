@extends('layouts.app')

@section('title', 'Historial de Ventas')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
        <div>
            <h3 class="fw-extrabold text-dark mb-1">Historial de Ventas y Comprobantes</h3>
            <p class="text-muted small mb-0">Consulta y reimprime todas las transacciones generadas en caja</p>
        </div>
        <a href="{{ route('pos.index') }}" class="btn btn-primary rounded-pill px-3 py-2 fw-bold d-flex align-items-center gap-2 shadow-sm">
            <i class="fas fa-shopping-cart"></i> <span>Ir al Punto de Venta</span>
        </a>
    </div>

    <!-- KPIs del Filtro -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-4">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <span class="text-muted fw-bold small text-uppercase mb-1">Recaudación en Período</span>
                <h3 class="fw-extrabold text-success mb-0">{{ $config->moneda ?? 'S/' }} {{ number_format($totalRecaudado, 2) }}</h3>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <span class="text-muted fw-bold small text-uppercase mb-1">Tickets Emitidos</span>
                <h3 class="fw-extrabold text-primary mb-0">{{ number_format($cantVentas) }} ventas</h3>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <span class="text-muted fw-bold small text-uppercase mb-1">Ticket Promedio</span>
                <h3 class="fw-extrabold text-dark mb-0">{{ $config->moneda ?? 'S/' }} {{ number_format($ticketPromedio, 2) }}</h3>
            </div>
        </div>
    </div>

    <!-- Filtros de Búsqueda -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form action="{{ route('pos.historial') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-bold text-secondary mb-1">Desde</label>
                    <input type="date" name="desde" class="form-control rounded-3" value="{{ $desde }}">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-bold text-secondary mb-1">Hasta</label>
                    <input type="date" name="hasta" class="form-control rounded-3" value="{{ $hasta }}">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold text-secondary mb-1">Buscar Cliente / Cajero</label>
                    <input type="text" name="busqueda" class="form-control rounded-3" placeholder="Nombre de cliente o cajero..." value="{{ $busqueda }}">
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-3 w-100 fw-bold">
                        <i class="fas fa-filter me-1"></i> Filtrar
                    </button>
                    <a href="{{ route('pos.historial') }}" class="btn btn-light border rounded-3" title="Limpiar Filtro">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Ventas -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="text-muted small text-uppercase">
                            <th># Ticket</th>
                            <th>Fecha y Hora</th>
                            <th>Sucursal</th>
                            <th>Cliente</th>
                            <th>Cajero</th>
                            <th>Método</th>
                            <th>Total</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ventas as $v)
                            <tr>
                                <td class="fw-bold text-primary">#{{ str_pad($v->id, 5, '0', STR_PAD_LEFT) }}</td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $v->fecha->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ $v->fecha->format('H:i:s') }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <i class="fas fa-store text-primary me-1"></i> {{ $v->sucursal->nombre ?? ($v->caja->sucursal->nombre ?? 'Sede Principal') }}
                                    </span>
                                </td>
                                <td>
                                    @if($v->socio)
                                        <div class="fw-semibold text-dark">{{ $v->socio->nombre }}</div>
                                        <small class="text-muted">DNI: {{ $v->socio->dni }}</small>
                                    @else
                                        <span class="text-muted fst-italic">Venta Directa</span>
                                    @endif
                                </td>
                                <td>{{ $v->caja->usuario->nombre ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border rounded-pill px-2 py-1 small">
                                        <i class="{{ $v->metodo_pago_icono }} me-1"></i>
                                        {{ $v->metodo_pago_nombre }}
                                    </span>
                                </td>
                                <td class="fw-bold text-dark fs-6">{{ $config->moneda ?? 'S/' }} {{ number_format($v->total, 2) }}</td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-light rounded-pill px-3 me-1" onclick="verDetalle({{ $v->id }})">
                                        <i class="fas fa-eye me-1"></i> Ver
                                    </button>
                                    <a href="{{ route('ticket.ver', $v->id) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3" title="Imprimir Ticket">
                                        <i class="fas fa-print me-1"></i> Ticket
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-receipt fa-3x mb-2 opacity-50"></i>
                                    <p class="mb-0">No se encontraron ventas para los criterios seleccionados.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="d-flex justify-content-end mt-4">
                {{ $ventas->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalle de Venta -->
<div class="modal fade" id="modalDetalleVenta" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalVentaTitle">Detalle de Venta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="modalVentaBody">
                <div class="text-center py-4">
                    <span class="spinner-border text-primary"></span>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
                <a href="#" id="modalPrintLink" target="_blank" class="btn btn-primary rounded-pill px-4">
                    <i class="fas fa-print me-1"></i> Imprimir
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function verDetalle(id) {
    const modal = new bootstrap.Modal(document.getElementById('modalDetalleVenta'));
    const body = document.getElementById('modalVentaBody');
    const title = document.getElementById('modalVentaTitle');
    const printLink = document.getElementById('modalPrintLink');

    title.textContent = `Venta #${id.toString().padStart(5, '0')}`;
    printLink.href = `/ticket/${id}`;
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
                        <span class="text-muted">Sucursal:</span>
                        <strong class="text-dark">${data.sucursal ? data.sucursal.nombre : 'Sede Principal'}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Cliente:</span>
                        <strong class="text-dark">${data.socio ? data.socio.nombre : 'Venta Directa'}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Cajero:</span>
                        <strong class="text-dark">${data.caja && data.caja.usuario ? data.caja.usuario.nombre : 'N/A'}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Método de Pago:</span>
                        <strong class="text-dark">${metodoTexto}</strong>
                    </div>
                </div>

                <h6 class="fw-bold small text-uppercase text-secondary mb-2">Artículos Vendidos</h6>
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
                        <span class="text-primary">${moneda} ${parseFloat(data.total).toFixed(2)}</span>
                    </div>
                </div>
            `;

            body.innerHTML = html;
        })
        .catch(err => {
            body.innerHTML = '<div class="alert alert-danger mb-0">Error al cargar detalles de la venta.</div>';
        });
}
</script>
@endpush
