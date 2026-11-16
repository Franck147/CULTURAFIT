@extends('layouts.app')

@section('title', 'Historial de Ventas - ' . $socio->nombre)

@section('content')
<div class="container-fluid">
    <!-- Socio Card Header -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    @if($socio->foto && file_exists(public_path('img/socios/' . $socio->foto)))
                        <img src="/img/socios/{{ $socio->foto }}" alt="{{ $socio->nombre }}" class="rounded-circle object-fit-cover shadow-sm" width="64" height="64">
                    @else
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-extrabold d-flex align-items-center justify-content-center fs-4" style="width: 64px; height: 64px;">
                            {{ strtoupper(substr($socio->nombre, 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h4 class="fw-extrabold text-dark mb-0">{{ $socio->nombre }}</h4>
                            <span class="badge rounded-pill {{ $socio->estado === 'activo' ? 'bg-success' : 'bg-secondary' }} px-3 py-1">
                                {{ ucfirst($socio->estado) }}
                            </span>
                        </div>
                        <div class="d-flex flex-wrap gap-3 small text-muted">
                            <span><i class="fas fa-id-card me-1"></i> DNI: <strong>{{ $socio->dni }}</strong></span>
                            @if($socio->telefono)
                                <span><i class="fas fa-phone-alt me-1"></i> {{ $socio->telefono }}</span>
                            @endif
                            @if($socio->email)
                                <span><i class="fas fa-envelope me-1"></i> {{ $socio->email }}</span>
                            @endif
                            <span><i class="fas fa-calendar-alt me-1"></i> Registro: {{ $socio->fecha_registro ? $socio->fecha_registro->format('d/m/Y') : '-' }}</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('socios.index') }}" class="btn btn-light rounded-pill px-3 fw-semibold">
                        <i class="fas fa-arrow-left me-1"></i> Volver a Clientes
                    </a>
                    <a href="{{ route('pos.index') }}" class="btn btn-primary rounded-pill px-3 fw-semibold shadow-sm">
                        <i class="fas fa-shopping-cart me-1"></i> Nueva Venta en POS
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- KPIs de Compras del Cliente -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted fw-bold small text-uppercase">Total Comprado</span>
                    <div class="rounded-circle bg-success bg-opacity-10 text-success p-2">
                        <i class="fas fa-wallet fa-fw fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-extrabold text-success mb-1">{{ $config->moneda ?? 'S/' }} {{ number_format($totalComprado, 2) }}</h3>
                <span class="text-muted small">Acumulado en compras</span>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted fw-bold small text-uppercase">N° de Compras</span>
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2">
                        <i class="fas fa-receipt fa-fw fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-extrabold text-primary mb-1">{{ number_format($cantVentas) }} tickets</h3>
                <span class="text-muted small">Transacciones realizadas</span>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted fw-bold small text-uppercase">Ticket Promedio</span>
                    <div class="rounded-circle bg-info bg-opacity-10 text-info p-2">
                        <i class="fas fa-chart-line fa-fw fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-extrabold text-dark mb-1">{{ $config->moneda ?? 'S/' }} {{ number_format($ticketPromedio, 2) }}</h3>
                <span class="text-muted small">Gasto medio por pedido</span>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted fw-bold small text-uppercase">Última Compra</span>
                    <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-2">
                        <i class="fas fa-calendar-check fa-fw fs-5"></i>
                    </div>
                </div>
                <h4 class="fw-extrabold text-dark mb-1">{{ $ultimaVenta ? $ultimaVenta->fecha->format('d/m/Y') : 'Sin compras' }}</h4>
                <span class="text-muted small">{{ $ultimaVenta ? $ultimaVenta->fecha->diffForHumans() : 'Cliente nuevo' }}</span>
            </div>
        </div>
    </div>

    <!-- Contenido Principal: Historial y Top Productos -->
    <div class="row g-4">
        <!-- Columna Izquierda: Tabla Historial de Ventas -->
        <div class="col-12 col-xl-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-0 p-4 pb-0 d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">
                            <i class="fas fa-history text-primary me-2"></i>Historial de Ventas al Cliente
                        </h5>
                        <p class="text-muted small mb-0">Detalle de todos los tickets emitidos y suplementos adquiridos</p>
                    </div>
                    <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill">
                        {{ $cantVentas }} {{ $cantVentas === 1 ? 'registro' : 'registros' }}
                    </span>
                </div>
                <div class="card-body p-4">
                    @if($ventas->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr class="text-muted small text-uppercase">
                                        <th># Ticket</th>
                                        <th>Fecha y Hora</th>
                                        <th>Suplementos Adquiridos</th>
                                        <th>Método</th>
                                        <th>Total</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ventas as $v)
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary bg-opacity-10 text-primary fw-bold font-monospace px-2 py-1">
                                                    #{{ str_pad($v->id, 5, '0', STR_PAD_LEFT) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-dark">{{ $v->fecha->format('d/m/Y') }}</div>
                                                <small class="text-muted">{{ $v->fecha->format('H:i') }} ({{ $v->fecha->diffForHumans() }})</small>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-1" style="max-width: 280px;">
                                                    @foreach($v->detalles as $det)
                                                        <span class="badge bg-light text-dark border fw-normal" style="font-size: 0.78rem;">
                                                            <strong>{{ $det->cantidad }}x</strong> {{ $det->producto->nombre ?? 'Producto' }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border rounded-pill px-2 py-1 small">
                                                    <i class="{{ $v->metodo_pago_icono }} me-1"></i>
                                                    {{ $v->metodo_pago_nombre }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-dark fs-6">
                                                    {{ $config->moneda ?? 'S/' }} {{ number_format($v->total, 2) }}
                                                </span>
                                                @if($v->descuento > 0)
                                                    <div><small class="text-danger">-{{ $config->moneda ?? 'S/' }} {{ number_format($v->descuento, 2) }}</small></div>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <div class="d-flex justify-content-end gap-1">
                                                    <button type="button" class="btn btn-sm btn-light rounded-pill px-3" onclick="verDetalle({{ $v->id }})" title="Ver Resumen de Venta">
                                                        <i class="fas fa-eye me-1 text-muted"></i> Ver
                                                    </button>
                                                    <a href="{{ route('ticket.ver', $v->id) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3" title="Imprimir Comprobante 80mm">
                                                        <i class="fas fa-print me-1"></i> Ticket
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <div class="d-flex justify-content-end mt-4">
                            {{ $ventas->links('pagination::bootstrap-5') }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex p-4 mb-3">
                                <i class="fas fa-shopping-bag fa-3x"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">Sin historial de compras</h5>
                            <p class="text-muted small mb-3">Este cliente aún no registra ventas ni compras en la tienda de suplementos.</p>
                            <a href="{{ route('pos.index') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm">
                                <i class="fas fa-shopping-cart me-1"></i> Realizar Venta en el POS
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Top Suplementos & Ficha del Cliente -->
        <div class="col-12 col-xl-4">
            <!-- Top Suplementos Preferidos -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-transparent border-0 p-4 pb-0">
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="fas fa-fire text-warning me-2"></i>Suplementos Más Comprados
                    </h5>
                    <p class="text-muted small mb-0">Preferencia y consumo habitual del cliente</p>
                </div>
                <div class="card-body p-4">
                    @if($topProductos->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($topProductos as $index => $tp)
                                <div class="list-group-item px-0 py-3 d-flex align-items-center justify-content-between border-bottom">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle bg-warning bg-opacity-10 text-dark fw-extrabold d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; font-size: 0.85rem;">
                                            #{{ $index + 1 }}
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.9rem;">
                                                {{ $tp->producto->nombre ?? 'Producto Eliminado' }}
                                            </h6>
                                            <small class="text-muted">{{ $tp->total_cantidad }} {{ $tp->total_cantidad == 1 ? 'unidad adquirida' : 'unidades adquiridas' }}</small>
                                        </div>
                                    </div>
                                    <span class="fw-extrabold text-primary small">
                                        {{ $config->moneda ?? 'S/' }} {{ number_format($tp->total_gastado, 2) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-cubes fa-2x mb-2 opacity-40"></i>
                            <p class="small mb-0">No hay productos suficientes para calcular sus preferencias.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Ficha Rápida del Cliente -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-0 p-4 pb-0">
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="fas fa-address-card text-info me-2"></i>Contacto & Fidelización
                    </h5>
                    <p class="text-muted small mb-0">Acciones rápidas para atención al cliente</p>
                </div>
                <div class="card-body p-4">
                    <ul class="list-unstyled mb-4 small">
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Estado del Cliente:</span>
                            <span class="badge {{ $socio->estado === 'activo' ? 'bg-success' : 'bg-secondary' }} rounded-pill px-2 py-1">
                                {{ ucfirst($socio->estado) }}
                            </span>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Documento DNI:</span>
                            <strong class="text-dark font-monospace">{{ $socio->dni }}</strong>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Teléfono / WhatsApp:</span>
                            <strong class="text-dark">{{ $socio->telefono ?? 'No registrado' }}</strong>
                        </li>
                        <li class="d-flex justify-content-between py-2">
                            <span class="text-muted">Correo Electrónico:</span>
                            <span class="text-dark text-truncate" style="max-width: 170px;">{{ $socio->email ?? 'No registrado' }}</span>
                        </li>
                    </ul>

                    @if($socio->telefono)
                        <a href="https://wa.me/51{{ preg_replace('/[^0-9]/', '', $socio->telefono) }}" target="_blank" class="btn btn-outline-success rounded-pill w-100 fw-semibold mb-2 d-flex align-items-center justify-content-center gap-2">
                            <i class="fab fa-whatsapp fs-5"></i> <span>Contactar por WhatsApp</span>
                        </a>
                    @endif

                    <a href="{{ route('socios.editar', $socio->id) }}" class="btn btn-light border rounded-pill w-100 fw-semibold d-flex align-items-center justify-content-center gap-2">
                        <i class="fas fa-user-edit text-muted"></i> <span>Editar Datos del Cliente</span>
                    </a>
                </div>
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
                    <i class="fas fa-print me-1"></i> Imprimir Ticket
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

    title.textContent = `Comprobante de Venta #${id.toString().padStart(5, '0')}`;
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

                <h6 class="fw-bold small text-uppercase text-secondary mb-2">Suplementos / Artículos</h6>
                <div class="list-group list-group-flush mb-3">
            `;

            if (data.detalles && data.detalles.length > 0) {
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
            } else {
                html += '<div class="text-muted small py-2">Sin detalles de artículos</div>';
            }

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
