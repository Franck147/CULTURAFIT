@extends('layouts.app')

@section('title', 'Usuarios del Sistema')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
        <div>
            <h3 class="fw-extrabold text-dark mb-1">Usuarios del Sistema</h3>
            <p class="text-muted small mb-0">Control de accesos y roles (Administrador y Cajero / Personal Asignado)</p>
        </div>
        <a href="{{ route('usuarios.crear') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-bold d-flex align-items-center gap-2 shadow-sm">
            <i class="fas fa-user-plus"></i> <span>Nuevo Usuario</span>
        </a>
    </div>

    <!-- Tabla de Usuarios -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle table-data mb-0">
                    <thead class="table-light">
                        <tr class="text-muted small text-uppercase">
                            <th>Usuario</th>
                            <th>Correo Electrónico</th>
                            <th>Rol / Permisos</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($usuarios as $u)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                            {{ strtoupper(substr($u->nombre, 0, 1)) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark">{{ $u->nombre }}</h6>
                                            <small class="text-muted">ID: #{{ $u->id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $u->email }}</td>
                                <td>
                                    @if($u->rol === 'admin')
                                        <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-1 rounded-pill fw-bold text-uppercase">
                                            <i class="fas fa-shield-alt me-1"></i> Administrador
                                        </span>
                                    @else
                                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-pill fw-bold text-uppercase">
                                            <i class="fas fa-cash-register me-1"></i> Cajero / Asignado
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge rounded-pill {{ $u->estado === 'activo' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ ucfirst($u->estado) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="{{ route('usuarios.editar', $u->id) }}" class="btn btn-sm btn-light text-secondary rounded-circle p-2" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        @if($u->id !== auth()->id())
                                            <form action="{{ route('usuarios.cambiar-estado', $u->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-light text-{{ $u->estado === 'activo' ? 'danger' : 'success' }} rounded-circle p-2" title="{{ $u->estado === 'activo' ? 'Inhabilitar' : 'Habilitar' }}">
                                                    <i class="fas fa-{{ $u->estado === 'activo' ? 'user-slash' : 'user-check' }}"></i>
                                                </button>
                                            </form>
                                        @endif
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
@endsection
