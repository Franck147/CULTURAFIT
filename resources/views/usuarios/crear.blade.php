@extends('layouts.app')

@section('title', 'Nuevo Usuario')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-6">
            <!-- Header -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h3 class="fw-extrabold text-dark mb-1">Registrar Nuevo Usuario</h3>
                    <p class="text-muted small mb-0">Crea credenciales de acceso para el personal del gimnasio</p>
                </div>
                <a href="{{ route('usuarios.index') }}" class="btn btn-light rounded-pill px-3">
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

                    <form action="{{ route('usuarios.guardar') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary mb-1">Nombre Completo <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control rounded-3" placeholder="Ej: Roberto Salas" value="{{ old('nombre') }}" required autofocus>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary mb-1">Correo Electrónico (Login) <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control rounded-3" placeholder="roberto@gym.com" value="{{ old('email') }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary mb-1">Contraseña Inicial <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control rounded-3" placeholder="Mínimo 6 caracteres" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary mb-1">Rol / Permisos <span class="text-danger">*</span></label>
                            <select name="rol" class="form-select rounded-3" required>
                                <option value="admin" {{ old('rol') === 'admin' ? 'selected' : '' }}>👑 Administrador (Acceso total al sistema)</option>
                                <option value="cajero" {{ old('rol') === 'cajero' ? 'selected' : '' }}>💳 Cajero / Personal de Tienda (Caja, POS, Clientes, Gastos)</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-secondary mb-1">
                                Sucursal Asignada <span class="text-muted fw-normal">(Tienda donde opera)</span>
                            </label>
                            <select name="sucursal_id" class="form-select rounded-3">
                                <option value="">-- Sin sucursal fija / Todas (Recomendado para Admin) --</option>
                                @foreach($sucursales as $suc)
                                    <option value="{{ $suc->id }}" {{ old('sucursal_id') == $suc->id ? 'selected' : '' }}>
                                        📍 {{ $suc->nombre }} ({{ $suc->ciudad ?? 'Lima' }}) {{ $suc->es_principal ? '★ Sede Principal' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted" style="font-size: 0.72rem;">Los cajeros asignados a una sucursal emitirán tickets con la dirección y datos de esa tienda</small>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('usuarios.index') }}" class="btn btn-light rounded-pill px-4">Cancelar</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                                <i class="fas fa-save me-1"></i> Guardar Usuario
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
