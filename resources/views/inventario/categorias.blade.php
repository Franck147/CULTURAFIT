@extends('layouts.app')

@section('title', 'Categorías de Productos')

@section('content')
<div class="container-fluid">
    <div class="row g-4">
        <!-- Formulario Crear/Editar Categoría -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-1" id="formCatTitle">Nueva Categoría</h5>
                    <p class="text-muted small mb-4">Organiza tus productos por familias y categorías</p>

                    <form action="{{ route('inventario.categorias.guardar') }}" method="POST" id="formCategoria">
                        @csrf
                        <input type="hidden" name="id" id="cat_id">

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-secondary mb-1">Nombre de la Categoría <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="cat_nombre" class="form-control rounded-3" placeholder="Ej: Suplementos, Ropa Deportiva..." required autofocus>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light rounded-pill px-3 d-none" id="btnCancelEdit" onclick="cancelarEdicion()">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold flex-grow-1 shadow-sm">
                                <i class="fas fa-save me-1"></i> Guardar Categoría
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tabla de Categorías -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 p-4 pb-0 d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="fw-bold mb-1">Categorías Registradas</h5>
                        <span class="text-muted small">{{ count($categorias) }} categorías en el sistema</span>
                    </div>
                    <a href="{{ route('inventario.productos') }}" class="btn btn-outline-primary rounded-pill btn-sm px-3 fw-bold">
                        <i class="fas fa-boxes me-1"></i> Ver Productos
                    </a>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-data mb-0">
                            <thead class="table-light">
                                <tr class="text-muted small text-uppercase">
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Productos Asociados</th>
                                    <th>Estado</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categorias as $c)
                                    <tr>
                                        <td class="fw-bold">#{{ $c->id }}</td>
                                        <td class="fw-bold text-dark">{{ $c->nombre }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark border px-2 py-1">
                                                {{ $c->productos->count() }} productos
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge rounded-pill {{ $c->estado === 'activo' ? 'bg-success' : 'bg-secondary' }}">
                                                {{ ucfirst($c->estado) }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end gap-1">
                                                <button class="btn btn-sm btn-light text-secondary rounded-circle p-2" title="Editar" onclick="editarCat({{ $c->id }}, '{{ addslashes($c->nombre) }}')">
                                                    <i class="fas fa-edit"></i>
                                                </button>

                                                <form action="{{ route('inventario.categorias.cambiar-estado', $c->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-light text-{{ $c->estado === 'activo' ? 'danger' : 'success' }} rounded-circle p-2" title="{{ $c->estado === 'activo' ? 'Desactivar' : 'Activar' }}">
                                                        <i class="fas fa-{{ $c->estado === 'activo' ? 'ban' : 'check' }}"></i>
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
    </div>
</div>

<script>
function editarCat(id, nombre) {
    document.getElementById('formCatTitle').textContent = 'Editar Categoría';
    document.getElementById('cat_id').value = id;
    document.getElementById('cat_nombre').value = nombre;
    document.getElementById('btnCancelEdit').classList.remove('d-none');
    document.getElementById('cat_nombre').focus();
}

function cancelarEdicion() {
    document.getElementById('formCatTitle').textContent = 'Nueva Categoría';
    document.getElementById('cat_id').value = '';
    document.getElementById('cat_nombre').value = '';
    document.getElementById('btnCancelEdit').classList.add('d-none');
}
</script>
@endsection
