@extends('layouts.app')

@section('title', 'Punto de Venta (POS)')

@section('content')
<div class="container-fluid">
    <div class="row g-3">
        <!-- Columna Izquierda: Catálogo y Búsqueda -->
        <div class="col-12 col-xl-8">
            <div class="card border-0 shadow-sm rounded-4 h-100 d-flex flex-column">
                <div class="card-header bg-transparent border-0 p-4 pb-2">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                        <div>
                            <h4 class="fw-extrabold text-dark mb-1">Catálogo de Productos</h4>
                            <span class="text-muted small">Selecciona un producto para agregarlo a la orden</span>
                        </div>
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold">
                            <i class="fas fa-cash-register me-1"></i> Caja #{{ $cajaAbierta->id }}
                        </span>
                    </div>

                    <!-- Buscador y Filtro de Categorías -->
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-search"></i></span>
                                <input type="text" id="posSearch" class="form-control bg-light border-start-0" placeholder="Buscar por nombre o código de barra...">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="d-flex gap-1 overflow-x-auto pb-1" id="categoryPills">
                                <button class="btn btn-sm btn-primary rounded-pill px-3 cat-filter active" data-cat="all">Todas</button>
                                @foreach($categorias as $cat)
                                    <button class="btn btn-sm btn-light border rounded-pill px-3 cat-filter text-nowrap" data-cat="{{ $cat->id }}">{{ $cat->nombre }}</button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grid de Productos -->
                <div class="card-body p-4 pt-2 flex-grow-1 overflow-y-auto" style="max-height: calc(100vh - 270px);">
                    <div class="row g-3" id="productGrid">
                        @forelse($productos as $p)
                            <div class="col-6 col-sm-4 col-md-3 product-item" data-cat="{{ $p->categoria_id }}" data-name="{{ strtolower($p->nombre) }}" data-code="{{ strtolower($p->codigo ?? '') }}">
                                <div class="card h-100 border rounded-4 product-card p-3 d-flex flex-column justify-content-between shadow-xs {{ $p->stock <= 0 ? 'opacity-50 pointer-events-none' : '' }}" 
                                     onclick="addToCart({{ $p->id }}, '{{ addslashes($p->nombre) }}', {{ $p->precio_venta }}, {{ $p->stock }})">
                                    <div class="text-center mb-2">
                                        @if($p->foto && file_exists(public_path('img/productos/' . $p->foto)))
                                            <img src="/img/productos/{{ $p->foto }}" alt="{{ $p->nombre }}" class="img-fluid rounded-3 object-fit-cover" style="height: 90px; width: 100%;">
                                        @else
                                            <div class="bg-light rounded-3 d-flex align-items-center justify-content-center text-muted" style="height: 90px;">
                                                <i class="fas fa-box fs-2 opacity-50"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="d-flex justify-content-between align-items-start gap-1 mb-1">
                                            <span class="badge {{ $p->stock > 5 ? 'bg-success' : ($p->stock > 0 ? 'bg-warning text-dark' : 'bg-danger') }} bg-opacity-10 {{ $p->stock > 5 ? 'text-success' : ($p->stock > 0 ? 'text-dark' : 'text-danger') }} rounded-pill px-2 py-1" style="font-size: 0.68rem;">
                                                Stock: {{ $p->stock }}
                                            </span>
                                            @if($p->codigo)
                                                <span class="text-muted" style="font-size: 0.68rem;">{{ $p->codigo }}</span>
                                            @endif
                                        </div>
                                        <h6 class="fw-bold text-dark text-truncate mb-1" style="font-size: 0.88rem;" title="{{ $p->nombre }}">{{ $p->nombre }}</h6>
                                        <div class="fw-extrabold text-primary fs-6">{{ $config->moneda ?? 'S/' }} {{ number_format($p->precio_venta, 2) }}</div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-5 text-muted">
                                <i class="fas fa-box-open fa-3x mb-2 opacity-50"></i>
                                <p class="mb-0">No hay productos registrados en el inventario.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Carrito y Checkout -->
        <div class="col-12 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 d-flex flex-column">
                <div class="card-header bg-transparent border-0 p-4 pb-2">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="fw-bold mb-0">Detalle de la Venta</h5>
                        <button class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="clearCart()">
                            <i class="fas fa-trash-alt me-1"></i> Vaciar
                        </button>
                    </div>

                    <!-- Sección de Cliente / Búsqueda por DNI -->
                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-bold text-secondary mb-0">Cliente</label>
                            <span class="badge bg-light text-muted fw-normal" id="clientBadgeStatus" style="font-size: 0.72rem;">
                                <i class="fas fa-user text-secondary me-1"></i>Venta Directa
                            </span>
                        </div>

                        <!-- ID del socio seleccionado para el cobro -->
                        <input type="hidden" id="socioSelect" value="">

                        <!-- Buscador directo por DNI -->
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light text-muted border-end-0">
                                <i class="fas fa-id-card"></i>
                            </span>
                            <input type="text" 
                                   id="posDniSearch" 
                                   class="form-control border-start-0 border-end-0 ps-1" 
                                   placeholder="Ingresa DNI para buscar cliente..." 
                                   maxlength="20" 
                                   autocomplete="off"
                                   oninput="handlePosDniInputChange(this)"
                                   onkeydown="if(event.key==='Enter'){event.preventDefault(); buscarClientePorDniPos();}">
                            <button class="btn btn-outline-primary px-3 fw-semibold d-inline-flex align-items-center gap-1" 
                                    type="button" 
                                    id="btnBuscarDniPos" 
                                    onclick="buscarClientePorDniPos()">
                                <span id="btnBuscarDniPosIcon"><i class="fas fa-search"></i></span>
                                <span>Buscar</span>
                            </button>
                        </div>

                        <!-- Contenedor dinámico de resultado de búsqueda por DNI -->
                        <div id="posDniResultBox" class="mt-2 d-none"></div>
                    </div>
                </div>

                <!-- Lista de Ítems en Carrito -->
                <div class="card-body p-4 pt-1 flex-grow-1 overflow-y-auto" id="cartContainer" style="max-height: 280px;">
                    <div id="cartEmptyState" class="text-center py-5 text-muted">
                        <i class="fas fa-shopping-basket fa-3x mb-2 opacity-40"></i>
                        <p class="small mb-0">El carrito está vacío.<br>Haz clic en los productos para agregarlos.</p>
                    </div>
                    <ul class="list-group list-group-flush" id="cartList"></ul>
                </div>

                <!-- Totales y Métodos de Pago -->
                <div class="card-footer bg-light border-0 p-4 rounded-bottom-4">
                    <div class="d-flex justify-content-between small text-muted mb-2">
                        <span>Subtotal</span>
                        <span id="cartSubtotalText">{{ $config->moneda ?? 'S/' }} 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <span class="small text-muted fw-semibold">Descuento</span>
                            <small class="d-block text-muted" style="font-size: 0.68rem;" title="El descuento se registrará automáticamente en Gastos apuntando a este ticket">
                                <i class="fas fa-tags text-primary"></i> Se refleja en Gastos
                            </small>
                        </div>
                        <div class="input-group input-group-sm" style="width: 130px;">
                            <span class="input-group-text bg-white text-muted fw-semibold">{{ $config->moneda ?? 'S/' }}</span>
                            <input type="number" step="0.50" min="0" id="cartDiscount" class="form-control text-end fw-bold text-danger" value="0.00" placeholder="0.00" oninput="updateTotals()">
                        </div>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold fs-5 text-dark">Total a Pagar</span>
                        <span class="fw-extrabold fs-4 text-primary" id="cartTotalText">{{ $config->moneda ?? 'S/' }} 0.00</span>
                    </div>

                    <!-- Métodos de Pago -->
                    <div class="mb-3 payment-method-selector">
                        <label class="form-label small fw-bold text-secondary mb-2">Método de Pago</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="metodo_pago" id="pagoEfectivo" value="efectivo" checked onchange="handlePaymentMethodChange()">
                                <label class="btn-payment w-100" for="pagoEfectivo">
                                    <i class="fas fa-money-bill-wave text-success"></i> <span>Efectivo</span>
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="metodo_pago" id="pagoTarjeta" value="tarjeta" onchange="handlePaymentMethodChange()">
                                <label class="btn-payment w-100" for="pagoTarjeta">
                                    <i class="fas fa-credit-card text-primary"></i> <span>Tarjeta</span>
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="metodo_pago" id="pagoYape" value="yape_plin" onchange="handlePaymentMethodChange()">
                                <label class="btn-payment w-100" for="pagoYape">
                                    <i class="fas fa-qrcode" style="color: #8B5CF6;"></i> <span>Yape / Plin</span>
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="metodo_pago" id="pagoTransf" value="transferencia" onchange="handlePaymentMethodChange()">
                                <label class="btn-payment w-100" for="pagoTransf">
                                    <i class="fas fa-university text-info"></i> <span>Transf.</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Calculadora de Vuelto para Efectivo -->
                    <div id="cashCalculator" class="p-3 bg-white border rounded-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-semibold text-muted">Paga con:</span>
                            <div class="input-group input-group-sm" style="width: 130px;">
                                <span class="input-group-text">{{ $config->moneda ?? 'S/' }}</span>
                                <input type="number" step="0.50" min="0" id="cashReceived" class="form-control text-end" placeholder="0.00" oninput="calculateChange()">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center small">
                            <span class="fw-bold text-muted">Vuelto / Cambio:</span>
                            <span class="fw-extrabold text-success fs-6" id="cashChangeText">{{ $config->moneda ?? 'S/' }} 0.00</span>
                        </div>
                    </div>

                    <!-- Botón Cobrar -->
                    <button class="btn btn-primary w-100 py-3 rounded-pill fw-bold fs-6 shadow-sm d-flex align-items-center justify-content-center gap-2" id="btnCheckout" onclick="processCheckout()">
                        <i class="fas fa-check-circle"></i> Finalizar Cobro
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Registro Rápido de Cliente en POS -->
<div class="modal fade" id="modalNuevoCliente" tabindex="-1" aria-labelledby="modalNuevoClienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 d-inline-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="fas fa-user-plus fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="modalNuevoClienteLabel">Nuevo Cliente</h5>
                        <p class="text-muted small mb-0">Registro rápido para asociar a la venta en curso</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body p-4">
                <form id="formNuevoCliente" onsubmit="event.preventDefault(); guardarClienteRapido();">
                    <!-- DNI y Botón Buscar RENIEC -->
                    <div class="mb-3">
                        <label for="nuevoClienteDni" class="form-label small fw-bold text-secondary mb-1">
                            DNI / Documento <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-id-card"></i></span>
                            <input type="text" 
                                   id="nuevoClienteDni" 
                                   class="form-control border-start-0 ps-1" 
                                   placeholder="Ingresa DNI (8 dígitos)" 
                                   maxlength="20" 
                                   autocomplete="off"
                                   oninput="handleDniInput(this)"
                                   onkeydown="if(event.key==='Enter'){event.preventDefault(); consultarDniCliente();}">
                            <button class="btn btn-outline-primary px-3 fw-semibold d-inline-flex align-items-center gap-2" 
                                    type="button" 
                                    id="btnConsultarDni" 
                                    onclick="consultarDniCliente()">
                                <span id="btnConsultarDniIcon"><i class="fas fa-search"></i></span>
                                <span id="btnConsultarDniText">Buscar DNI</span>
                            </button>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <small class="text-muted" style="font-size: 0.74rem;">
                                <i class="fas fa-shield-alt text-primary me-1"></i>Consulta RENIEC o completa los campos manualmente.
                            </small>
                            <span id="dniCounter" class="badge bg-light text-muted fw-normal" style="font-size: 0.7rem;">0 dígitos</span>
                        </div>
                        <div id="dniAlerta" class="alert small py-2 px-3 mt-2 d-none rounded-3" role="alert"></div>
                    </div>

                    <!-- Nombre Completo (Siempre editable) -->
                    <div class="mb-3">
                        <label for="nuevoClienteNombre" class="form-label small fw-bold text-secondary mb-1">
                            Nombre Completo <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-user"></i></span>
                            <input type="text" 
                                   id="nuevoClienteNombre" 
                                   class="form-control border-start-0 ps-1" 
                                   placeholder="Apellidos y Nombres" 
                                   maxlength="100" 
                                   required>
                        </div>
                        <small class="text-muted" style="font-size: 0.74rem;">Editable en caso de que el API no responda o requiera ajustes.</small>
                    </div>

                    <!-- Teléfono y Correo Electrónico -->
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label for="nuevoClienteTelefono" class="form-label small fw-bold text-secondary mb-1">
                                Celular / WhatsApp <span class="text-muted fw-normal">(Opcional)</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-success border-end-0"><i class="fab fa-whatsapp"></i></span>
                                <input type="text" 
                                       id="nuevoClienteTelefono" 
                                       class="form-control border-start-0 ps-1" 
                                       placeholder="Ej. 987654321" 
                                       maxlength="20">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="nuevoClienteEmail" class="form-label small fw-bold text-secondary mb-1">
                                Correo <span class="text-muted fw-normal">(Opcional)</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-envelope"></i></span>
                                <input type="email" 
                                       id="nuevoClienteEmail" 
                                       class="form-control border-start-0 ps-1" 
                                       placeholder="correo@ejemplo.com" 
                                       maxlength="100">
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer border-0 pt-0 pb-4 px-4 d-flex justify-content-between">
                <button type="button" class="btn btn-light rounded-pill px-3 fw-semibold text-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm d-inline-flex align-items-center gap-2" id="btnGuardarClienteModal" onclick="guardarClienteRapido()">
                    <span id="btnGuardarClienteIcon"><i class="fas fa-check-circle"></i></span>
                    <span id="btnGuardarClienteText">Guardar y Seleccionar</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Resumen y Confirmación de Venta -->
<div class="modal fade" id="modalResumenVenta" tabindex="-1" aria-labelledby="modalResumenVentaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <!-- Modal Header -->
            <div class="modal-header border-0 bg-light pb-3 pt-4 px-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 d-inline-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
                        <i class="fas fa-receipt fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="modalResumenVentaLabel">Resumen de la Venta</h5>
                        <p class="text-muted small mb-0">Revisa y confirma los detalles antes de registrar la venta</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4">
                <!-- Información del Cliente y Método de Pago -->
                <div class="row g-3 mb-3">
                    <!-- Cliente Asignado -->
                    <div class="col-12 col-md-6">
                        <div class="p-3 rounded-4 bg-light h-100 border border-light-subtle">
                            <span class="text-secondary small fw-bold text-uppercase d-block mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                                <i class="fas fa-user me-1 text-primary"></i> Cliente / Receptor
                            </span>
                            <div class="fw-bold text-dark fs-6 text-truncate" id="resumenClienteNombre">
                                Cliente General / Venta Directa
                            </div>
                            <div class="small text-muted mt-1" id="resumenClienteDoc">
                                <span class="badge bg-light text-muted">Venta Libre (Sin DNI)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Método de Pago -->
                    <div class="col-12 col-md-6">
                        <div class="p-3 rounded-4 bg-light h-100 border border-light-subtle">
                            <span class="text-secondary small fw-bold text-uppercase d-block mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                                <i class="fas fa-credit-card me-1 text-primary"></i> Método de Pago
                            </span>
                            <div class="d-flex align-items-center gap-2" id="resumenMetodoBadge">
                                <!-- Badge inyectado dinámicamente -->
                            </div>
                            <div class="small text-muted mt-1" id="resumenDetallePago">
                                <!-- Detalle inyectado dinámicamente -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista de Productos a Vender -->
                <div class="card border border-light-subtle rounded-4 overflow-hidden mb-3">
                    <div class="card-header bg-white py-2 px-3 border-0 border-bottom d-flex justify-content-between align-items-center">
                        <span class="small fw-bold text-secondary text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                            <i class="fas fa-boxes me-1 text-primary"></i> Productos en la Orden (<span id="resumenTotalItems">0</span> ítems)
                        </span>
                        <span class="badge bg-light text-muted fw-normal" id="resumenTotalUnidades">0 unidades</span>
                    </div>
                    <div class="table-responsive" style="max-height: 220px;">
                        <table class="table table-hover table-sm align-middle mb-0">
                            <thead class="table-light small text-muted">
                                <tr>
                                    <th class="ps-3 py-2">Producto</th>
                                    <th class="text-center py-2" style="width: 80px;">Cant.</th>
                                    <th class="text-end py-2" style="width: 100px;">P. Unit.</th>
                                    <th class="text-end pe-3 py-2" style="width: 110px;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="resumenTablaProductos" class="small">
                                <!-- Filas inyectadas -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Totales y Descuentos -->
                <div class="p-3 rounded-4 bg-light border border-light-subtle">
                    <div class="d-flex justify-content-between text-muted small mb-1">
                        <span>Subtotal (Precios Fijos de Catálogo):</span>
                        <span class="fw-semibold text-dark" id="resumenSubtotal">S/ 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between text-muted small mb-2" id="resumenDescuentoRow">
                        <span>Descuento Comercial:</span>
                        <span class="fw-semibold text-danger" id="resumenDescuento">- S/ 0.00</span>
                    </div>
                    <!-- Aviso de Gasto por Descuento -->
                    <div id="resumenGastoAviso" class="alert alert-warning py-2 px-3 small rounded-3 mb-2 d-none" style="font-size: 0.78rem;">
                        <i class="fas fa-tags text-warning me-1"></i>
                        <strong>Control de Gastos:</strong> Este descuento de <strong id="resumenGastoAvisoMonto">S/ 0.00</strong> se registrará automáticamente en <strong>Gastos y Salidas</strong> apuntando al ticket, manteniendo la caja 100% cuadrada.
                    </div>
                    <hr class="my-2 border-secondary border-opacity-25">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fw-bold fs-5 text-dark">Total a Cobrar</span>
                            <small class="text-muted d-block" style="font-size: 0.75rem;">Monto neto que ingresa a caja</small>
                        </div>
                        <span class="fw-extrabold fs-3 text-primary" id="resumenTotalPagar">S/ 0.00</span>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-0 bg-light py-3 px-4 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">
                    <i class="fas fa-arrow-left me-1"></i> Modificar Venta
                </button>
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm d-inline-flex align-items-center gap-2" id="btnConfirmarVentaFinal" onclick="confirmarVentaFinal()">
                    <span id="btnConfirmarIcon"><i class="fas fa-check-circle"></i></span>
                    <span id="btnConfirmarText">Confirmar y Finalizar Venta</span>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .product-card {
        cursor: pointer;
        transition: all 0.2s ease;
        border-color: #E2E8F0 !important;
    }
    .product-card:hover {
        transform: translateY(-3px);
        border-color: var(--gym-primary) !important;
        box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.15);
    }
    .cat-filter.active {
        background-color: var(--gym-primary) !important;
        color: #ffffff !important;
        border-color: var(--gym-primary) !important;
    }

    /* POS Payment Selector High Contrast Styling */
    .payment-method-selector .btn-payment {
        background-color: #FFFFFF;
        border: 1.5px solid #E2E8F0;
        color: #334155 !important;
        font-weight: 600;
        font-size: 0.86rem;
        padding: 0.6rem 0.75rem;
        border-radius: 12px !important;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        text-decoration: none;
        user-select: none;
    }
    .payment-method-selector .btn-payment:hover {
        background-color: #F8FAFC;
        border-color: #CBD5E1;
        color: #0F172A !important;
        transform: translateY(-1px);
    }
    /* Efectivo Seleccionado (Verde Esmeralda) */
    .payment-method-selector .btn-check:checked + label[for="pagoEfectivo"] {
        background: linear-gradient(135deg, #10B981, #059669) !important;
        border-color: #059669 !important;
        color: #FFFFFF !important;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35) !important;
    }
    /* Tarjeta Seleccionada (Azul Zafiro) */
    .payment-method-selector .btn-check:checked + label[for="pagoTarjeta"] {
        background: linear-gradient(135deg, #3B82F6, #2563EB) !important;
        border-color: #2563EB !important;
        color: #FFFFFF !important;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35) !important;
    }
    /* Yape / Plin Seleccionado (Morado Vibrante) */
    .payment-method-selector .btn-check:checked + label[for="pagoYape"] {
        background: linear-gradient(135deg, #8B5CF6, #7C3AED) !important;
        border-color: #7C3AED !important;
        color: #FFFFFF !important;
        box-shadow: 0 4px 12px rgba(124, 58, 237, 0.35) !important;
    }
    /* Transferencia Seleccionada (Cian Corporativo) */
    .payment-method-selector .btn-check:checked + label[for="pagoTransf"] {
        background: linear-gradient(135deg, #0EA5E9, #0284C7) !important;
        border-color: #0284C7 !important;
        color: #FFFFFF !important;
        box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35) !important;
    }
    /* Garantizar que el ícono y el texto sean blanco puro en cualquier botón activo */
    .payment-method-selector .btn-check:checked + .btn-payment,
    .payment-method-selector .btn-check:checked + .btn-payment span,
    .payment-method-selector .btn-check:checked + .btn-payment i {
        color: #FFFFFF !important;
    }
</style>
@endsection

@push('scripts')
<script>
let cart = [];
const moneda = '{{ $config->moneda ?? "S/" }}';

function addToCart(id, name, price, maxStock) {
    const existing = cart.find(i => i.id === id);
    if (existing) {
        if (existing.qty + 1 > maxStock) {
            Swal.fire({
                icon: 'warning',
                title: 'Stock Límite',
                text: `Solo hay ${maxStock} unidades disponibles.`
            });
            return;
        }
        existing.qty += 1;
    } else {
        if (maxStock <= 0) {
            Swal.fire({ icon: 'error', title: 'Sin Stock', text: 'Este producto está agotado.' });
            return;
        }
        cart.push({ id, name, price: parseFloat(price), qty: 1, maxStock });
    }
    renderCart();
}

function updateQty(id, delta) {
    const item = cart.find(i => i.id === id);
    if (!item) return;

    const newQty = item.qty + delta;
    if (newQty <= 0) {
        removeFromCart(id);
        return;
    }
    if (newQty > item.maxStock) {
        Swal.fire({ icon: 'warning', title: 'Stock Límite', text: `Solo hay ${item.maxStock} unidades disponibles.` });
        return;
    }
    item.qty = newQty;
    renderCart();
}

function removeFromCart(id) {
    cart = cart.filter(i => i.id !== id);
    renderCart();
}

function clearCart() {
    if (cart.length === 0) return;
    cart = [];
    document.getElementById('cartDiscount').value = '0.00';
    document.getElementById('cashReceived').value = '';
    deseleccionarClientePos();
    renderCart();
}

function renderCart() {
    const list = document.getElementById('cartList');
    const emptyState = document.getElementById('cartEmptyState');

    list.innerHTML = '';

    if (cart.length === 0) {
        emptyState.classList.remove('d-none');
    } else {
        emptyState.classList.add('d-none');
        cart.forEach(item => {
            const li = document.createElement('li');
            li.className = 'list-group-item px-0 py-2 d-flex align-items-center justify-content-between border-bottom';
            li.innerHTML = `
                <div class="overflow-hidden pe-2" style="max-width: 55%;">
                    <h6 class="mb-0 fw-semibold text-dark text-truncate small">${item.name}</h6>
                    <small class="text-muted">${moneda} ${item.price.toFixed(2)} c/u</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="input-group input-group-sm" style="width: 86px;">
                        <button class="btn btn-outline-secondary px-2" type="button" onclick="updateQty(${item.id}, -1)">-</button>
                        <span class="input-group-text bg-white px-2 fw-bold">${item.qty}</span>
                        <button class="btn btn-outline-secondary px-2" type="button" onclick="updateQty(${item.id}, 1)">+</button>
                    </div>
                    <span class="fw-bold text-dark small text-end" style="min-width: 60px;">${moneda} ${(item.price * item.qty).toFixed(2)}</span>
                    <button class="btn btn-link text-danger p-0 ms-1" onclick="removeFromCart(${item.id})"><i class="fas fa-times"></i></button>
                </div>
            `;
            list.appendChild(li);
        });
    }

    updateTotals();
}

function updateTotals() {
    const subtotal = cart.reduce((acc, i) => acc + (i.price * i.qty), 0);
    const discount = parseFloat(document.getElementById('cartDiscount').value) || 0;
    const total = Math.max(0, subtotal - discount);

    document.getElementById('cartSubtotalText').textContent = `${moneda} ${subtotal.toFixed(2)}`;
    document.getElementById('cartTotalText').textContent = `${moneda} ${total.toFixed(2)}`;

    calculateChange();
}

function handlePaymentMethodChange() {
    const isEfectivo = document.getElementById('pagoEfectivo').checked;
    const calc = document.getElementById('cashCalculator');
    if (isEfectivo) {
        calc.classList.remove('d-none');
    } else {
        calc.classList.add('d-none');
    }
}

function calculateChange() {
    const subtotal = cart.reduce((acc, i) => acc + (i.price * i.qty), 0);
    const discount = parseFloat(document.getElementById('cartDiscount').value) || 0;
    const total = Math.max(0, subtotal - discount);

    const received = parseFloat(document.getElementById('cashReceived').value) || 0;
    const change = Math.max(0, received - total);

    document.getElementById('cashChangeText').textContent = `${moneda} ${change.toFixed(2)}`;
}

// Búsqueda y Filtros
document.getElementById('posSearch').addEventListener('input', filterProducts);

document.querySelectorAll('.cat-filter').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.cat-filter').forEach(b => b.classList.remove('active', 'btn-primary'));
        document.querySelectorAll('.cat-filter').forEach(b => b.classList.add('btn-light'));
        this.classList.add('active', 'btn-primary');
        this.classList.remove('btn-light');
        filterProducts();
    });
});

function filterProducts() {
    const query = document.getElementById('posSearch').value.toLowerCase().trim();
    const activeCat = document.querySelector('.cat-filter.active').dataset.cat;

    document.querySelectorAll('.product-item').forEach(el => {
        const name = el.dataset.name;
        const code = el.dataset.code;
        const cat = el.dataset.cat;

        const matchesQuery = name.includes(query) || code.includes(query);
        const matchesCat = (activeCat === 'all' || cat === activeCat);

        if (matchesQuery && matchesCat) {
            el.classList.remove('d-none');
        } else {
            el.classList.add('d-none');
        }
    });
}

// Validar carrito y abrir modal de resumen de la venta
function processCheckout() {
    if (cart.length === 0) {
        Swal.fire({ icon: 'warning', title: 'Carrito Vacío', text: 'Agrega al menos un producto a la venta.' });
        return;
    }

    const subtotal = cart.reduce((acc, i) => acc + (i.price * i.qty), 0);
    const discount = parseFloat(document.getElementById('cartDiscount').value) || 0;
    const total = Math.max(0, subtotal - discount);

    const metodoPago = document.querySelector('input[name="metodo_pago"]:checked').value;
    const socioSelect = document.getElementById('socioSelect');
    const socioId = socioSelect.value || null;

    let cashReceived = 0;
    let cashChange = 0;

    if (metodoPago === 'efectivo') {
        cashReceived = parseFloat(document.getElementById('cashReceived').value) || 0;
        if (cashReceived > 0 && cashReceived < total) {
            Swal.fire({
                icon: 'warning',
                title: 'Monto Recibido Insuficiente',
                text: `El monto recibido (${moneda} ${cashReceived.toFixed(2)}) es menor al total (${moneda} ${total.toFixed(2)}).`
            });
            return;
        }
        cashChange = Math.max(0, cashReceived - total);
    }

    // 1. Armar datos del cliente
    const resumenClienteNombre = document.getElementById('resumenClienteNombre');
    const resumenClienteDoc = document.getElementById('resumenClienteDoc');
    if (socioId && currentClient) {
        resumenClienteNombre.textContent = currentClient.nombre;
        resumenClienteDoc.innerHTML = `<span class="badge bg-primary bg-opacity-10 text-primary fw-semibold"><i class="fas fa-id-card me-1"></i>DNI: ${currentClient.dni || '-'}</span>`;
    } else {
        resumenClienteNombre.textContent = 'Cliente General / Venta Directa';
        resumenClienteDoc.innerHTML = `<span class="badge bg-light text-muted fw-normal"><i class="fas fa-user-tag me-1"></i>Venta Libre (Sin DNI)</span>`;
    }

    // 2. Armar badge de método de pago
    const resumenMetodoBadge = document.getElementById('resumenMetodoBadge');
    const resumenDetallePago = document.getElementById('resumenDetallePago');

    if (metodoPago === 'efectivo') {
        resumenMetodoBadge.innerHTML = `<span class="badge rounded-pill bg-success px-3 py-2 fw-bold fs-6"><i class="fas fa-money-bill-wave me-1"></i> Efectivo</span>`;
        if (cashReceived > 0) {
            resumenDetallePago.innerHTML = `Paga con: <strong>${moneda} ${cashReceived.toFixed(2)}</strong> &bull; Vuelto: <strong class="text-success">${moneda} ${cashChange.toFixed(2)}</strong>`;
        } else {
            resumenDetallePago.innerHTML = `Monto exacto: <strong>${moneda} ${total.toFixed(2)}</strong>`;
        }
    } else if (metodoPago === 'yape_plin') {
        resumenMetodoBadge.innerHTML = `<span class="badge rounded-pill px-3 py-2 fw-bold fs-6 text-white" style="background: linear-gradient(135deg, #8B5CF6, #7C3AED);"><i class="fas fa-qrcode me-1"></i> Yape / Plin</span>`;
        resumenDetallePago.innerHTML = `<span class="text-secondary">Pago digital instantáneo vía QR o número</span>`;
    } else if (metodoPago === 'tarjeta') {
        resumenMetodoBadge.innerHTML = `<span class="badge rounded-pill bg-primary px-3 py-2 fw-bold fs-6"><i class="fas fa-credit-card me-1"></i> Tarjeta</span>`;
        resumenDetallePago.innerHTML = `<span class="text-secondary">Cobro registrado mediante Terminal POS</span>`;
    } else if (metodoPago === 'transferencia') {
        resumenMetodoBadge.innerHTML = `<span class="badge rounded-pill px-3 py-2 fw-bold fs-6 text-white" style="background: #0284C7;"><i class="fas fa-university me-1"></i> Transferencia</span>`;
        resumenDetallePago.innerHTML = `<span class="text-secondary">Depósito o transferencia bancaria en cuenta</span>`;
    }

    // 3. Tabla de Productos
    const tbody = document.getElementById('resumenTablaProductos');
    tbody.innerHTML = '';
    let totalUnidades = 0;

    cart.forEach(item => {
        totalUnidades += item.qty;
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="ps-3 py-2 fw-semibold text-dark">${item.name}</td>
            <td class="text-center py-2"><span class="badge bg-light text-dark fw-bold border px-2 py-1">${item.qty}</span></td>
            <td class="text-end py-2 text-muted">${moneda} ${item.price.toFixed(2)}</td>
            <td class="text-end pe-3 py-2 fw-bold text-dark">${moneda} ${(item.price * item.qty).toFixed(2)}</td>
        `;
        tbody.appendChild(row);
    });

    document.getElementById('resumenTotalItems').textContent = cart.length;
    document.getElementById('resumenTotalUnidades').textContent = `${totalUnidades} ${totalUnidades === 1 ? 'unidad' : 'unidades'}`;

    // 4. Totales
    document.getElementById('resumenSubtotal').textContent = `${moneda} ${subtotal.toFixed(2)}`;
    const descRow = document.getElementById('resumenDescuentoRow');
    const gastoAviso = document.getElementById('resumenGastoAviso');
    const gastoAvisoMonto = document.getElementById('resumenGastoAvisoMonto');
    if (discount > 0) {
        descRow.classList.remove('d-none');
        document.getElementById('resumenDescuento').textContent = `- ${moneda} ${discount.toFixed(2)}`;
        if (gastoAviso && gastoAvisoMonto) {
            gastoAvisoMonto.textContent = `${moneda} ${discount.toFixed(2)}`;
            gastoAviso.classList.remove('d-none');
        }
    } else {
        descRow.classList.add('d-none');
        if (gastoAviso) {
            gastoAviso.classList.add('d-none');
        }
    }
    document.getElementById('resumenTotalPagar').textContent = `${moneda} ${total.toFixed(2)}`;

    // 5. Abrir el modal de resumen
    const modal = new bootstrap.Modal(document.getElementById('modalResumenVenta'));
    modal.show();
}

// Confirmar y procesar venta final tras revisar el resumen
function confirmarVentaFinal() {
    const subtotal = cart.reduce((acc, i) => acc + (i.price * i.qty), 0);
    const discount = parseFloat(document.getElementById('cartDiscount').value) || 0;
    const metodoPago = document.querySelector('input[name="metodo_pago"]:checked').value;
    const socioId = document.getElementById('socioSelect').value || null;

    const btn = document.getElementById('btnConfirmarVentaFinal');
    const icon = document.getElementById('btnConfirmarIcon');
    const text = document.getElementById('btnConfirmarText');

    btn.disabled = true;
    icon.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';
    text.textContent = 'Procesando Venta...';

    const payload = {
        carrito: JSON.stringify(cart.map(i => ({ id: i.id, cantidad: i.qty }))),
        metodo_pago: metodoPago,
        socio_id: socioId,
        descuento: discount
    };

    fetch('{{ route("pos.procesar") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        icon.innerHTML = '<i class="fas fa-check-circle"></i>';
        text.textContent = 'Confirmar y Finalizar Venta';

        if (data.status === 'success') {
            // Cerrar modal de resumen
            const modalEl = document.getElementById('modalResumenVenta');
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) {
                modalInstance.hide();
            }

            Swal.fire({
                icon: 'success',
                title: '¡Venta Registrada con Éxito!',
                text: '¿Deseas imprimir el comprobante de venta?',
                showCancelButton: true,
                confirmButtonColor: '#6366F1',
                cancelButtonColor: '#64748B',
                confirmButtonText: '<i class="fas fa-print me-1"></i> Imprimir Ticket',
                cancelButtonText: 'Nueva Venta'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.open(data.redirect_url, '_blank');
                }
                clearCart();
            });
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'No se pudo completar la venta.' });
        }
    })
    .catch(err => {
        btn.disabled = false;
        icon.innerHTML = '<i class="fas fa-check-circle"></i>';
        text.textContent = 'Confirmar y Finalizar Venta';
        Swal.fire({ icon: 'error', title: 'Error de Red', text: 'Ocurrió un error al procesar la solicitud.' });
    });
}

// ==========================================
// REGISTRO RÁPIDO DE CLIENTES Y CONSULTA DNI
// ==========================================
function handleDniInput(el) {
    const counter = document.getElementById('dniCounter');
    if (counter) {
        counter.textContent = `${el.value.length} dígitos`;
    }
    hideDniAlert();
}

function showDniAlert(type, message, htmlExtra = '') {
    const alerta = document.getElementById('dniAlerta');
    if (!alerta) return;
    alerta.className = `alert alert-${type} small py-2 px-3 mt-2 rounded-3 border-0`;
    alerta.innerHTML = message + (htmlExtra ? `<div class="mt-2">${htmlExtra}</div>` : '');
    alerta.classList.remove('d-none');
}

function hideDniAlert() {
    const alerta = document.getElementById('dniAlerta');
    if (alerta) {
        alerta.classList.add('d-none');
        alerta.innerHTML = '';
    }
}

function consultarDniCliente() {
    const dniInput = document.getElementById('nuevoClienteDni');
    const dni = dniInput.value.trim();

    if (!dni) {
        showDniAlert('warning', '<i class="fas fa-exclamation-triangle me-1"></i> Por favor ingresa un número de DNI.');
        dniInput.focus();
        return;
    }

    const btn = document.getElementById('btnConsultarDni');
    const icon = document.getElementById('btnConsultarDniIcon');
    const text = document.getElementById('btnConsultarDniText');

    btn.disabled = true;
    icon.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
    text.textContent = 'Buscando...';
    hideDniAlert();

    fetch('{{ route("pos.consultar-dni") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ dni: dni })
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        icon.innerHTML = '<i class="fas fa-search"></i>';
        text.textContent = 'Buscar DNI';

        if (data.status === 'exists') {
            const socio = data.socio;
            document.getElementById('nuevoClienteNombre').value = socio.nombre;
            if (socio.telefono) document.getElementById('nuevoClienteTelefono').value = socio.telefono;
            if (socio.email) document.getElementById('nuevoClienteEmail').value = socio.email;

            const safeNombre = socio.nombre.replace(/'/g, "\\'");
            showDniAlert('info', 
                `<strong><i class="fas fa-user-check me-1"></i> Cliente ya registrado:</strong> ${socio.nombre}`,
                `<button type="button" class="btn btn-sm btn-primary rounded-pill px-3" onclick="seleccionarClienteExistente(${socio.id}, '${safeNombre}', '${socio.dni}')">
                    <i class="fas fa-check me-1"></i> Asignar a esta Venta
                </button>`
            );
        } else if (data.status === 'found') {
            document.getElementById('nuevoClienteNombre').value = data.nombre;
            showDniAlert('success', `<i class="fas fa-check-circle me-1"></i> Nombre obtenido desde RENIEC: <strong>${data.nombre}</strong>`);
            document.getElementById('nuevoClienteTelefono').focus();
        } else {
            showDniAlert('secondary', '<i class="fas fa-pencil-alt me-1"></i> ' + (data.message || 'DNI no encontrado en RENIEC. Puedes completar los campos manualmente.'));
            document.getElementById('nuevoClienteNombre').focus();
        }
    })
    .catch(err => {
        btn.disabled = false;
        icon.innerHTML = '<i class="fas fa-search"></i>';
        text.textContent = 'Buscar DNI';
        showDniAlert('secondary', '<i class="fas fa-pencil-alt me-1"></i> No se pudo consultar RENIEC en este momento. Puedes ingresar el nombre manualmente.');
        document.getElementById('nuevoClienteNombre').focus();
    });
}

let currentClient = null;

function seleccionarClienteExistente(id, nombre, dni, notify = true) {
    currentClient = { id, nombre, dni };
    const socioHidden = document.getElementById('socioSelect');
    if (socioHidden) socioHidden.value = id;

    // Actualizar input DNI y mostrar caja de cliente confirmado
    const dniSearch = document.getElementById('posDniSearch');
    if (dniSearch && dni) dniSearch.value = dni;

    // Actualizar badge indicador
    const badge = document.getElementById('clientBadgeStatus');
    if (badge) {
        badge.className = 'badge bg-success bg-opacity-10 text-success fw-semibold';
        badge.innerHTML = '<i class="fas fa-check-circle me-1"></i>Identificado';
    }

    mostrarResultadoDniEncontrado(currentClient);

    // Cerrar modal si estuviera abierto
    const modalEl = document.getElementById('modalNuevoCliente');
    const modalInstance = bootstrap.Modal.getInstance(modalEl);
    if (modalInstance) {
        modalInstance.hide();
    }
    resetModalCliente();

    if (notify) {
        Swal.fire({
            icon: 'success',
            title: 'Cliente Asignado',
            text: `${nombre} seleccionado para esta venta.`,
            timer: 2000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    }
}

// Búsqueda por DNI debajo de Cliente General
function buscarClientePorDniPos() {
    const input = document.getElementById('posDniSearch');
    const dni = input.value.trim();

    if (!dni) {
        input.focus();
        return;
    }

    const btn = document.getElementById('btnBuscarDniPos');
    const icon = document.getElementById('btnBuscarDniPosIcon');

    btn.disabled = true;
    icon.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';

    fetch('{{ route("pos.consultar-dni") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ dni: dni })
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        icon.innerHTML = '<i class="fas fa-search"></i>';

        if (data.status === 'exists') {
            // El cliente ya existe en el sistema -> seleccionarlo directamente
            const socio = data.socio;
            seleccionarClienteExistente(socio.id, socio.nombre, socio.dni, true);
        } else {
            // El cliente NO existe -> recién mostrar opción para registrar nuevo cliente
            mostrarResultadoDniNoEncontrado(dni, data);
        }
    })
    .catch(err => {
        btn.disabled = false;
        icon.innerHTML = '<i class="fas fa-search"></i>';
        mostrarResultadoDniNoEncontrado(dni, { message: 'No se pudo verificar el DNI' });
    });
}

function mostrarResultadoDniEncontrado(socio) {
    const box = document.getElementById('posDniResultBox');
    if (!box) return;
    box.className = 'mt-2 p-2 rounded-3 bg-success bg-opacity-10 border border-success border-opacity-25 d-flex justify-content-between align-items-center';
    box.innerHTML = `
        <div class="small text-truncate me-2">
            <i class="fas fa-check-circle text-success me-1"></i>
            <strong class="text-dark">${socio.nombre}</strong>
            <span class="text-muted small d-block">DNI: ${socio.dni}</span>
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 rounded-pill" style="font-size: 0.72rem;" onclick="deseleccionarClientePos()" title="Quitar cliente">
            <i class="fas fa-times me-1"></i> Quitar
        </button>
    `;
    box.classList.remove('d-none');
}

function mostrarResultadoDniNoEncontrado(dni, data) {
    const box = document.getElementById('posDniResultBox');
    if (!box) return;
    box.className = 'mt-2 p-2 rounded-3 bg-warning bg-opacity-10 border border-warning border-opacity-25';
    box.innerHTML = `
        <div class="d-flex align-items-center justify-content-between mb-1">
            <span class="small fw-bold text-dark">
                <i class="fas fa-user-slash text-warning me-1"></i> Cliente no registrado
            </span>
            <span class="badge bg-warning text-dark fw-bold" style="font-size: 0.7rem;">DNI: ${dni}</span>
        </div>
        <p class="small text-muted mb-2" style="font-size: 0.75rem;">
            No existe en el sistema. Puedes agregarlo ahora para asociarlo a esta venta:
        </p>
        <button type="button" class="btn btn-sm btn-primary w-100 rounded-pill py-1 fw-bold shadow-sm d-inline-flex align-items-center justify-content-center gap-1" onclick="abrirModalNuevoClienteConDni('${dni}')">
            <i class="fas fa-user-plus"></i> + Registrar Nuevo Cliente
        </button>
    `;
    box.classList.remove('d-none');
}

function abrirModalNuevoClienteConDni(dni) {
    const modalEl = document.getElementById('modalNuevoCliente');
    const modal = new bootstrap.Modal(modalEl);
    
    // Asignar el DNI al modal
    const dniInput = document.getElementById('nuevoClienteDni');
    dniInput.value = dni;
    const counter = document.getElementById('dniCounter');
    if (counter) counter.textContent = `${dni.length} dígitos`;

    modal.show();

    // Consultar automáticamente RENIEC dentro del modal
    setTimeout(() => {
        consultarDniCliente();
    }, 350);
}

function deseleccionarClientePos() {
    currentClient = null;
    const socioHidden = document.getElementById('socioSelect');
    if (socioHidden) socioHidden.value = '';
    const input = document.getElementById('posDniSearch');
    if (input) input.value = '';
    const box = document.getElementById('posDniResultBox');
    if (box) {
        box.classList.add('d-none');
        box.innerHTML = '';
    }
    const badge = document.getElementById('clientBadgeStatus');
    if (badge) {
        badge.className = 'badge bg-light text-muted fw-normal';
        badge.innerHTML = '<i class="fas fa-user text-secondary me-1"></i>Venta Directa';
    }
}

function handlePosDniInputChange(el) {
    const box = document.getElementById('posDniResultBox');
    if (box && !box.classList.contains('d-none')) {
        if (box.innerHTML.includes('Cliente no registrado')) {
            box.classList.add('d-none');
        }
    }
}

function guardarClienteRapido() {
    const dniInput = document.getElementById('nuevoClienteDni');
    const nombreInput = document.getElementById('nuevoClienteNombre');
    const telefonoInput = document.getElementById('nuevoClienteTelefono');
    const emailInput = document.getElementById('nuevoClienteEmail');

    const dni = dniInput.value.trim();
    const nombre = nombreInput.value.trim();
    const telefono = telefonoInput.value.trim();
    const email = emailInput.value.trim();

    if (!dni) {
        showDniAlert('danger', '<i class="fas fa-exclamation-circle me-1"></i> El DNI o documento es obligatorio.');
        dniInput.focus();
        return;
    }

    if (!nombre) {
        showDniAlert('danger', '<i class="fas fa-exclamation-circle me-1"></i> El nombre completo es obligatorio.');
        nombreInput.focus();
        return;
    }

    const btn = document.getElementById('btnGuardarClienteModal');
    const icon = document.getElementById('btnGuardarClienteIcon');
    const text = document.getElementById('btnGuardarClienteText');

    btn.disabled = true;
    icon.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
    text.textContent = 'Guardando...';

    fetch('{{ route("pos.crear-cliente-rapido") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            dni: dni,
            nombre: nombre,
            telefono: telefono,
            email: email
        })
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        icon.innerHTML = '<i class="fas fa-check-circle"></i>';
        text.textContent = 'Guardar y Seleccionar';

        if (data.status === 'success' || data.status === 'exists') {
            const socio = data.socio;
            seleccionarClienteExistente(socio.id, socio.nombre, socio.dni);
        } else {
            showDniAlert('danger', `<i class="fas fa-times-circle me-1"></i> ${data.message || 'Error al guardar el cliente.'}`);
        }
    })
    .catch(err => {
        btn.disabled = false;
        icon.innerHTML = '<i class="fas fa-check-circle"></i>';
        text.textContent = 'Guardar y Seleccionar';
        showDniAlert('danger', '<i class="fas fa-times-circle me-1"></i> Error de comunicación con el servidor al registrar cliente.');
    });
}

function resetModalCliente() {
    document.getElementById('formNuevoCliente').reset();
    hideDniAlert();
    const counter = document.getElementById('dniCounter');
    if (counter) counter.textContent = '0 dígitos';
}

document.addEventListener('DOMContentLoaded', function() {
    const modalEl = document.getElementById('modalNuevoCliente');
    if (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', function () {
            resetModalCliente();
        });
        modalEl.addEventListener('shown.bs.modal', function () {
            document.getElementById('nuevoClienteDni').focus();
        });
    }
});
</script>
@endpush
