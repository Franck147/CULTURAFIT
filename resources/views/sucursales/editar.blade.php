@extends('layouts.app')

@section('title', 'Editar Sucursal - ' . $sucursal->nombre)

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8 col-xl-7">
            <!-- Header -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h3 class="fw-extrabold text-dark mb-1">Editar Sucursal</h3>
                    <p class="text-muted small mb-0">Modifica los datos del local, dirección para tickets y estado</p>
                </div>
                <a href="{{ route('sucursales.index') }}" class="btn btn-light rounded-pill px-3 fw-semibold">
                    <i class="fas fa-arrow-left me-1"></i> Volver
                </a>
            </div>

            <!-- Form Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4 p-md-5">
                    @if($errors->any())
                        <div class="alert alert-danger border-0 rounded-4 mb-4 small">
                            <ul class="mb-0">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('sucursales.actualizar', $sucursal->id) }}" method="POST">
                        @csrf

                        <!-- Nombre de Sucursal -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary mb-1">
                                Nombre de la Sucursal / Tienda <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="fas fa-store"></i></span>
                                <input type="text" name="nombre" class="form-control" placeholder="Ej: Sucursal San Borja" value="{{ old('nombre', $sucursal->nombre) }}" required>
                            </div>
                        </div>

                        <!-- Código y Ciudad -->
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-secondary mb-1">
                                    Código / Prefijo de Local
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="fas fa-barcode"></i></span>
                                    <input type="text" name="codigo" class="form-control" placeholder="Ej: SUC-02" value="{{ old('codigo', $sucursal->codigo) }}">
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-secondary mb-1">
                                    Ciudad / Distrito <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="fas fa-city"></i></span>
                                    <input type="text" name="ciudad" class="form-control" placeholder="Ej: Lima, San Borja..." value="{{ old('ciudad', $sucursal->ciudad) }}" required>
                                </div>
                            </div>
                        </div>

                        <!-- Dirección Completa -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary mb-1">
                                Dirección Física (Aparecerá en los Tickets de Venta) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="fas fa-map-marker-alt"></i></span>
                                <input type="text" name="direccion" class="form-control" placeholder="Ej: Av. Javier Prado Este 2450..." value="{{ old('direccion', $sucursal->direccion) }}" required>
                            </div>
                            <small class="text-muted" style="font-size: 0.72rem;">Esta dirección se imprimirá en los comprobantes de venta emitidos en este local</small>
                        </div>

                        <!-- Teléfono y Estado -->
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-secondary mb-1">
                                    Teléfono de la Sucursal
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="fas fa-phone-alt"></i></span>
                                    <input type="text" name="telefono" class="form-control" placeholder="Ej: 987654321 / 01-4458921" value="{{ old('telefono', $sucursal->telefono) }}">
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-secondary mb-1">
                                    Estado Operativo <span class="text-danger">*</span>
                                </label>
                                <select name="estado" class="form-select" required>
                                    <option value="activo" {{ old('estado', $sucursal->estado) === 'activo' ? 'selected' : '' }}>🟢 Activa (Disponible para ventas)</option>
                                    <option value="inactivo" {{ old('estado', $sucursal->estado) === 'inactivo' ? 'selected' : '' }}>🔴 Inactiva (Cerrada temporalmente)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Marcar como Principal -->
                        <div class="p-3 bg-light rounded-4 mb-4 border border-light-subtle">
                            <div class="form-check form-switch d-flex align-items-center gap-3 ps-0 mb-0">
                                <input class="form-check-input ms-0 mt-0" type="checkbox" role="switch" id="es_principal" name="es_principal" value="1" {{ old('es_principal', $sucursal->es_principal) ? 'checked' : '' }} style="width: 2.4em; height: 1.3em;">
                                <label class="form-check-label mb-0" for="es_principal">
                                    <strong class="d-block text-dark small">Definir como Sede Principal</strong>
                                    <span class="text-muted" style="font-size: 0.75rem;">Se usará por defecto si un cajero no tiene sucursal asignada</span>
                                </label>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('sucursales.index') }}" class="btn btn-light rounded-pill px-4 fw-semibold">Cancelar</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                                <i class="fas fa-save me-1"></i> Actualizar Sucursal
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Cajeros Asignados a esta Sucursal -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">
                            <i class="fas fa-users-cog text-primary me-2"></i>Cajeros Asignados a este Local
                        </h5>
                        <p class="text-muted small mb-0">Personal que opera el punto de venta en esta sucursal</p>
                    </div>
                    <a href="{{ route('usuarios.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        <i class="fas fa-user-plus me-1"></i> Asignar en Usuarios
                    </a>
                </div>
                <div class="card-body p-4">
                    @if($sucursal->cajeros->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($sucursal->cajeros as $caj)
                                <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                            {{ strtoupper(substr($caj->nombre, 0, 1)) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark">{{ $caj->nombre }}</h6>
                                            <small class="text-muted">{{ $caj->email }}</small>
                                        </div>
                                    </div>
                                    <span class="badge {{ $caj->estado === 'activo' ? 'bg-success' : 'bg-secondary' }} rounded-pill px-3 py-1">
                                        {{ ucfirst($caj->estado) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 text-muted small">
                            <i class="fas fa-user-slash fa-2x mb-2 opacity-40"></i>
                            <p class="mb-0">No hay cajeros asignados actualmente a esta sucursal.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
