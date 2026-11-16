@extends('layouts.app')

@section('title', 'Registrar Sucursal')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8 col-xl-7">
            <!-- Header -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h3 class="fw-extrabold text-dark mb-1">Registrar Nueva Sucursal</h3>
                    <p class="text-muted small mb-0">Agrega un local físico o punto de venta comercial</p>
                </div>
                <a href="{{ route('sucursales.index') }}" class="btn btn-light rounded-pill px-3 fw-semibold">
                    <i class="fas fa-arrow-left me-1"></i> Volver
                </a>
            </div>

            <!-- Form Card -->
            <div class="card border-0 shadow-sm rounded-4">
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

                    <form action="{{ route('sucursales.guardar') }}" method="POST">
                        @csrf

                        <!-- Nombre de Sucursal -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary mb-1">
                                Nombre de la Sucursal / Tienda <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="fas fa-store"></i></span>
                                <input type="text" name="nombre" class="form-control" placeholder="Ej: Sucursal San Borja, Sede Principal..." value="{{ old('nombre') }}" required autofocus>
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
                                    <input type="text" name="codigo" class="form-control" placeholder="Ej: SUC-02, SB-01" value="{{ old('codigo') }}">
                                </div>
                                <small class="text-muted" style="font-size: 0.72rem;">Opcional: código interno para identificar la tienda</small>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-secondary mb-1">
                                    Ciudad / Distrito <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="fas fa-city"></i></span>
                                    <input type="text" name="ciudad" class="form-control" placeholder="Ej: Lima, San Borja, Arequipa..." value="{{ old('ciudad', 'Lima') }}" required>
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
                                <input type="text" name="direccion" class="form-control" placeholder="Ej: Av. Javier Prado Este 2450, Centro Comercial..." value="{{ old('direccion') }}" required>
                            </div>
                            <small class="text-muted" style="font-size: 0.72rem;">Esta dirección se imprimirá en los vouchers de etiquetera de las ventas hechas en este local</small>
                        </div>

                        <!-- Teléfono y Estado -->
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-secondary mb-1">
                                    Teléfono de la Sucursal
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="fas fa-phone-alt"></i></span>
                                    <input type="text" name="telefono" class="form-control" placeholder="Ej: 987654321 / 01-4458921" value="{{ old('telefono') }}">
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-secondary mb-1">
                                    Estado Operativo <span class="text-danger">*</span>
                                </label>
                                <select name="estado" class="form-select" required>
                                    <option value="activo" {{ old('estado', 'activo') === 'activo' ? 'selected' : '' }}>🟢 Activa (Disponible para ventas)</option>
                                    <option value="inactivo" {{ old('estado') === 'inactivo' ? 'selected' : '' }}>🔴 Inactiva (Cerrada temporalmente)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Marcar como Principal -->
                        <div class="p-3 bg-light rounded-4 mb-4 border border-light-subtle">
                            <div class="form-check form-switch d-flex align-items-center gap-3 ps-0 mb-0">
                                <input class="form-check-input ms-0 mt-0" type="checkbox" role="switch" id="es_principal" name="es_principal" value="1" {{ old('es_principal') ? 'checked' : '' }} style="width: 2.4em; height: 1.3em;">
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
                                <i class="fas fa-save me-1"></i> Guardar Sucursal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
