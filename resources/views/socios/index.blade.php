@extends('layouts.app')

@section('title', 'Directorio de Clientes')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
        <div>
            <h3 class="fw-extrabold text-dark mb-1">Directorio de Clientes</h3>
            <p class="text-muted small mb-0">Gestión de clientes de la tienda, datos de contacto y fidelización</p>
        </div>
        <a href="{{ route('socios.crear') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-bold d-flex align-items-center gap-2 shadow-sm">
            <i class="fas fa-user-plus"></i> <span>Registrar Nuevo Cliente</span>
        </a>
    </div>

    <!-- Tabla de Clientes -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle table-data mb-0">
                    <thead class="table-light">
                        <tr class="text-muted small text-uppercase">
                            <th>Cliente</th>
                            <th>DNI / Identificación</th>
                            <th>Contacto</th>
                            <th>Fecha Registro</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($socios as $s)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        @if($s->foto && file_exists(public_path('img/socios/' . $s->foto)))
                                            <img src="/img/socios/{{ $s->foto }}" alt="{{ $s->nombre }}" class="rounded-circle object-fit-cover shadow-xs" width="44" height="44">
                                        @else
                                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; font-size: 1.1rem;">
                                                {{ strtoupper(substr($s->nombre, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark">{{ $s->nombre }}</h6>
                                            <small class="text-muted">ID: #{{ $s->id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-1 font-monospace">{{ $s->dni }}</span>
                                </td>
                                <td>
                                    <div><i class="fas fa-phone-alt text-muted small me-1"></i> {{ $s->telefono ?? '-' }}</div>
                                    <small class="text-muted"><i class="fas fa-envelope text-muted small me-1"></i> {{ $s->email ?? '-' }}</small>
                                </td>
                                <td>
                                    <div class="small fw-semibold text-dark">{{ $s->fecha_registro ? $s->fecha_registro->format('d/m/Y') : '-' }}</div>
                                    <small class="text-muted">{{ $s->fecha_registro ? $s->fecha_registro->diffForHumans() : '' }}</small>
                                </td>
                                <td>
                                    <span class="badge rounded-pill px-3 py-2 fw-bold {{ $s->estado === 'activo' ? 'bg-success bg-opacity-10 text-success' : ($s->estado === 'pendiente' ? 'bg-warning bg-opacity-10 text-warning text-dark' : 'bg-danger bg-opacity-10 text-danger') }}">
                                        {{ ucfirst($s->estado) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <!-- Ver Progreso y Rutinas -->
                                        <a href="{{ route('progreso.ver', $s->id) }}" class="btn btn-sm btn-light text-primary rounded-pill px-3 fw-semibold" title="Ver Progreso y Rutina">
                                            <i class="fas fa-chart-line me-1"></i> Progreso
                                        </a>

                                        <!-- Editar Socio -->
                                        <a href="{{ route('socios.editar', $s->id) }}" class="btn btn-sm btn-light text-secondary rounded-circle p-2" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <!-- Cambiar Estado -->
                                        <form action="{{ route('socios.cambiar-estado', $s->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-light text-{{ $s->estado === 'activo' ? 'danger' : 'success' }} rounded-circle p-2" title="{{ $s->estado === 'activo' ? 'Desactivar' : 'Activar' }}">
                                                <i class="fas fa-{{ $s->estado === 'activo' ? 'user-slash' : 'user-check' }}"></i>
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
@endsection
