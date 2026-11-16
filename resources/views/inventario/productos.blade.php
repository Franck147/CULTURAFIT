@extends('layouts.app')

@section('title', 'Inventario de Productos')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
        <div>
            <h3 class="fw-extrabold text-dark mb-1">Inventario de Productos</h3>
            <p class="text-muted small mb-0">Gestión de suplementos, bebidas, accesorios y control de existencias</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('inventario.categorias') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-bold">
                <i class="fas fa-tags me-1"></i> Categorías
            </a>
            <button class="btn btn-primary rounded-pill px-4 py-2 fw-bold d-flex align-items-center gap-2 shadow-sm" onclick="abrirModalProducto()">
                <i class="fas fa-plus"></i> <span>Nuevo Producto</span>
            </button>
        </div>
    </div>

    <!-- Tabla de Productos -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle table-data mb-0">
                    <thead class="table-light">
                        <tr class="text-muted small text-uppercase">
                            <th>Producto</th>
                            <th>Código</th>
                            <th>Categoría</th>
                            <th>P. Compra</th>
                            <th>P. Venta</th>
                            <th>Stock</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($productos as $p)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        @if($p->foto && file_exists(public_path('img/productos/' . $p->foto)))
                                            <img src="/img/productos/{{ $p->foto }}" alt="{{ $p->nombre }}" class="rounded-3 object-fit-cover shadow-xs" width="46" height="46">
                                        @else
                                            <div class="rounded-3 bg-light text-muted d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
                                                <i class="fas fa-box fs-5 opacity-50"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark">{{ $p->nombre }}</h6>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border font-monospace">{{ $p->codigo ?? 'S/C' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold">{{ $p->categoria->nombre ?? 'Sin Categoría' }}</span>
                                </td>
                                <td class="text-muted small">{{ $config->moneda ?? 'S/' }} {{ number_format($p->precio_compra, 2) }}</td>
                                <td class="fw-bold text-dark">{{ $config->moneda ?? 'S/' }} {{ number_format($p->precio_venta, 2) }}</td>
                                <td>
                                    <span class="badge rounded-pill px-3 py-1 fw-bold {{ $p->stock > 10 ? 'bg-success bg-opacity-10 text-success' : ($p->stock > 0 ? 'bg-warning bg-opacity-10 text-dark' : 'bg-danger bg-opacity-10 text-danger') }}">
                                        {{ $p->stock }} uds.
                                    </span>
                                </td>
                                <td>
                                    <span class="badge rounded-pill {{ $p->estado === 'activo' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ ucfirst($p->estado) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <!-- Ajustar Stock -->
                                        <button class="btn btn-sm btn-light text-primary rounded-circle p-2" title="Ajustar Stock" onclick="abrirModalStock({{ $p->id }}, '{{ addslashes($p->nombre) }}', {{ $p->stock }})">
                                            <i class="fas fa-layer-group"></i>
                                        </button>

                                        <!-- Editar Producto -->
                                        <button class="btn btn-sm btn-light text-secondary rounded-circle p-2" title="Editar" onclick='editarProducto(@json($p))'>
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <!-- Cambiar Estado -->
                                        <form action="{{ route('inventario.productos.cambiar-estado', $p->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-light text-{{ $p->estado === 'activo' ? 'danger' : 'success' }} rounded-circle p-2" title="{{ $p->estado === 'activo' ? 'Desactivar' : 'Activar' }}">
                                                <i class="fas fa-{{ $p->estado === 'activo' ? 'ban' : 'check' }}"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Producto (Crear / Editar) -->
<div class="modal fade" id="modalProducto" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalProdTitle">Nuevo Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('inventario.productos.guardar') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" id="prod_id">

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-8">
                            <label class="form-label small fw-bold text-secondary mb-1">Nombre del Producto <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="prod_nombre" class="form-control rounded-3" placeholder="Ej: Proteína Whey 2kg" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-secondary mb-1">Código de Barra / SKU</label>
                            <input type="text" name="codigo" id="prod_codigo" class="form-control rounded-3" placeholder="775123456789">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1">Categoría <span class="text-danger">*</span></label>
                            <select name="categoria_id" id="prod_cat" class="form-select rounded-3" required>
                                <option value="">-- Seleccione Categoría --</option>
                                @foreach($categorias as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6" id="divStockInicial">
                            <label class="form-label small fw-bold text-secondary mb-1">Stock Inicial</label>
                            <input type="number" name="stock" id="prod_stock" class="form-control rounded-3" min="0" value="0">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1">Precio Compra / Costo ({{ $config->moneda ?? 'S/' }}) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="precio_compra" id="prod_compra" class="form-control rounded-3" placeholder="0.00" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1">Precio Venta al Público ({{ $config->moneda ?? 'S/' }}) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="precio_venta" id="prod_venta" class="form-control rounded-3" placeholder="0.00" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-secondary mb-1">Imagen del Producto</label>
                            <input type="file" name="foto" class="form-control rounded-3" accept="image/jpeg,image/png,image/webp">
                            <small class="text-muted">Formatos: JPG, PNG, WEBP (Máx. 2MB)</small>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                        <i class="fas fa-save me-1"></i> Guardar Producto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ajuste de Stock -->
<div class="modal fade" id="modalAjusteStock" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Ajuste Rápido de Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('inventario.productos.ajuste-stock') }}" method="POST">
                @csrf
                <input type="hidden" name="producto_id" id="stock_prod_id">

                <div class="modal-body p-4">
                    <div class="p-3 bg-light rounded-3 mb-3">
                        <div class="fw-bold text-dark" id="stock_prod_nombre">Producto</div>
                        <div class="small text-muted">Stock actual: <strong id="stock_prod_actual" class="text-primary">0</strong> unidades</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary mb-1">Tipo de Operación</label>
                        <select name="operacion" class="form-select rounded-3" required>
                            <option value="sumar">➕ Entrada / Agregar Stock</option>
                            <option value="restar">➖ Salida / Retirar Stock (Mermas / Ajuste)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary mb-1">Cantidad a Ajustar</label>
                        <input type="number" min="1" name="cantidad" class="form-control rounded-3 fs-5 fw-bold" placeholder="1" required>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Confirmar Ajuste</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const modalProd = new bootstrap.Modal(document.getElementById('modalProducto'));
const modalStock = new bootstrap.Modal(document.getElementById('modalAjusteStock'));

function abrirModalProducto() {
    document.getElementById('modalProdTitle').textContent = 'Nuevo Producto';
    document.getElementById('prod_id').value = '';
    document.getElementById('prod_nombre').value = '';
    document.getElementById('prod_codigo').value = '';
    document.getElementById('prod_cat').value = '';
    document.getElementById('prod_stock').value = '0';
    document.getElementById('prod_compra').value = '';
    document.getElementById('prod_venta').value = '';
    document.getElementById('divStockInicial').classList.remove('d-none');
    modalProd.show();
}

function editarProducto(prod) {
    document.getElementById('modalProdTitle').textContent = 'Editar Producto';
    document.getElementById('prod_id').value = prod.id;
    document.getElementById('prod_nombre').value = prod.nombre;
    document.getElementById('prod_codigo').value = prod.codigo || '';
    document.getElementById('prod_cat').value = prod.categoria_id;
    document.getElementById('prod_compra').value = prod.precio_compra;
    document.getElementById('prod_venta').value = prod.precio_venta;
    document.getElementById('divStockInicial').classList.add('d-none'); // El stock se edita mediante ajuste
    modalProd.show();
}

function abrirModalStock(id, nombre, stock) {
    document.getElementById('stock_prod_id').value = id;
    document.getElementById('stock_prod_nombre').textContent = nombre;
    document.getElementById('stock_prod_actual').textContent = stock;
    modalStock.show();
}
</script>
@endpush
