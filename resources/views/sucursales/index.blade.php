@extends('layouts.app')

@section('title', 'Gestión de Sucursales')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
        <div>
            <h3 class="fw-extrabold text-dark mb-1">Sucursales & Tiendas Físicas</h3>
            <p class="text-muted small mb-0">Gestión de locales comerciales, direcciones para tickets y asignación de personal</p>
        </div>
        <a href="{{ route('sucursales.crear') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-bold d-flex align-items-center gap-2 shadow-sm">
            <i class="fas fa-plus-circle"></i> <span>Registrar Nueva Sucursal</span>
        </a>
    </div>

    <!-- KPIs de Sucursales -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted fw-bold small text-uppercase">Total Locales</span>
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2">
                        <i class="fas fa-store fa-fw fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-extrabold text-dark mb-1">{{ $totalSucursales }}</h3>
                <span class="text-muted small">Sucursales registradas</span>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted fw-bold small text-uppercase">Locales Activos</span>
                    <div class="rounded-circle bg-success bg-opacity-10 text-success p-2">
                        <i class="fas fa-check-circle fa-fw fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-extrabold text-success mb-1">{{ $sucursalesActivas }}</h3>
                <span class="text-muted small">Operativas para ventas</span>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted fw-bold small text-uppercase">Cajeros Asignados</span>
                    <div class="rounded-circle bg-info bg-opacity-10 text-info p-2">
                        <i class="fas fa-users-cog fa-fw fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-extrabold text-info mb-1">{{ $cajerosAsignados }} / {{ $totalCajeros }}</h3>
                <span class="text-muted small">Personal asignado a tiendas</span>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted fw-bold small text-uppercase">Sede Principal</span>
                    <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-2">
                        <i class="fas fa-star fa-fw fs-5"></i>
                    </div>
                </div>
                @php $principal = $sucursales->firstWhere('es_principal', true); @endphp
                <h5 class="fw-extrabold text-dark mb-1 text-truncate">{{ $principal ? $principal->nombre : 'Sin definir' }}</h5>
                <span class="text-muted small">{{ $principal ? ($principal->ciudad ?? 'Lima') : '-' }}</span>
            </div>
        </div>
    </div>

    <!-- Tabla de Sucursales -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle table-data mb-0">
                    <thead class="table-light">
                        <tr class="text-muted small text-uppercase">
                            <th>Sucursal</th>
                            <th>Código</th>
                            <th>Ubicación / Dirección</th>
                            <th>Teléfono</th>
                            <th>Cajeros Asignados</th>
                            <th>Ventas Totales</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sucursales as $suc)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-3 {{ $suc->es_principal ? 'bg-primary text-white' : 'bg-light text-primary border' }} fw-bold d-flex align-items-center justify-content-center shadow-xs" style="width: 44px; height: 44px; font-size: 1.1rem;">
                                            <i class="fas fa-store"></i>
                                        </div>
                                        <div>
                                            <div class="d-flex align-items-center gap-2">
                                                <h6 class="mb-0 fw-bold text-dark">{{ $suc->nombre }}</h6>
                                                @if($suc->es_principal)
                                                    <span class="badge bg-primary text-white px-2 py-1 rounded-pill" style="font-size: 0.68rem;">
                                                        <i class="fas fa-star text-warning me-1"></i> PRINCIPAL
                                                    </span>
                                                @endif
                                            </div>
                                            <small class="text-muted">{{ $suc->ciudad ?? 'Lima' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-1 font-monospace fw-bold">
                                        {{ $suc->codigo ?? 'S/C' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="small fw-semibold text-dark"><i class="fas fa-map-marker-alt text-danger me-1"></i> {{ $suc->direccion ?? 'Sin dirección' }}</div>
                                </td>
                                <td>
                                    <div class="small text-muted"><i class="fas fa-phone-alt me-1"></i> {{ $suc->telefono ?? '-' }}</div>
                                </td>
                                <td>
                                    @if($suc->cajeros_count > 0)
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-3 py-1 fw-bold">
                                            <i class="fas fa-user-check me-1"></i> {{ $suc->cajeros_count }} {{ $suc->cajeros_count === 1 ? 'Cajero' : 'Cajeros' }}
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border rounded-pill px-3 py-1">Sin cajeros</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-bold text-dark small">{{ number_format($suc->ventas_count) }} tickets</span>
                                </td>
                                <td>
                                    <span class="badge rounded-pill px-3 py-2 fw-bold {{ $suc->estado === 'activo' ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger' }}">
                                        {{ ucfirst($suc->estado) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <!-- Entrar a esta Sucursal (Cambiar Contexto) -->
                                        <form action="{{ route('sucursales.cambiar-contexto') }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="sucursal_contexto_id" value="{{ $suc->id }}">
                                            <button type="submit" class="btn btn-sm {{ session('sucursal_activa_id') == $suc->id ? 'btn-primary text-white shadow-xs' : 'btn-light text-primary' }} rounded-circle p-2" title="{{ session('sucursal_activa_id') == $suc->id ? 'Sucursal actualmente activa' : 'Ingresar a esta sucursal' }}">
                                                <i class="fas fa-sign-in-alt"></i>
                                            </button>
                                        </form>

                                        <!-- Editar Sucursal -->
                                        <a href="{{ route('sucursales.editar', $suc->id) }}" class="btn btn-sm btn-light text-secondary rounded-circle p-2" title="Editar Sucursal">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <!-- Cambiar Estado (Solo si no es principal) -->
                                        @if(!$suc->es_principal)
                                            <form action="{{ route('sucursales.cambiar-estado', $suc->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-light text-{{ $suc->estado === 'activo' ? 'danger' : 'success' }} rounded-circle p-2" title="{{ $suc->estado === 'activo' ? 'Desactivar' : 'Activar' }}">
                                                    <i class="fas fa-{{ $suc->estado === 'activo' ? 'ban' : 'check' }}"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-store-slash fa-3x mb-2 opacity-50"></i>
                                    <p class="mb-0">No hay sucursales registradas en el sistema.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
