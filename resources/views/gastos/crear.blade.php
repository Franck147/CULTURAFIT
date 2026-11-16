@extends('layouts.app')

@section('title', 'Nuevo Gasto')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-6">
            <!-- Header -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h3 class="fw-extrabold text-dark mb-1">Registrar Nuevo Gasto</h3>
                    <p class="text-muted small mb-0">Se descontará del efectivo disponible en el turno de caja actual</p>
                </div>
                <a href="{{ route('gastos.index') }}" class="btn btn-light rounded-pill px-3">
                    <i class="fas fa-arrow-left me-1"></i> Volver
                </a>
            </div>

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

                    <form action="{{ route('gastos.guardar') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary mb-1">Descripción / Concepto <span class="text-danger">*</span></label>
                            <input type="text" name="descripcion" class="form-control rounded-3" placeholder="Ej: Compra de agua para dispensador, limpieza..." value="{{ old('descripcion') }}" required autofocus>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-secondary mb-1">Monto ({{ $config->moneda ?? 'S/' }}) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted">{{ $config->moneda ?? 'S/' }}</span>
                                    <input type="number" step="0.01" min="0.01" name="monto" class="form-control rounded-end-3 fs-5 fw-bold text-danger" placeholder="0.00" value="{{ old('monto') }}" required>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-secondary mb-1">Fecha del Comprobante <span class="text-danger">*</span></label>
                                <input type="date" name="fecha" class="form-control rounded-3" value="{{ old('fecha', date('Y-m-d')) }}" required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('gastos.index') }}" class="btn btn-light rounded-pill px-4">Cancelar</a>
                            <button type="submit" class="btn btn-danger rounded-pill px-5 fw-bold shadow-sm">
                                <i class="fas fa-save me-1"></i> Guardar Gasto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
