@extends('layouts.app')

@section('title', 'Copias de Seguridad & Mantenimiento')

@section('content')
<div class="container-fluid">
    <!-- Header Principal -->
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
        <div>
            <h3 class="fw-extrabold text-dark mb-1">
                <i class="fas fa-database text-primary me-2"></i>Copias de Seguridad & Mantenimiento
            </h3>
            <p class="text-muted small mb-0">Gestión de respaldos periódicos, descarga de volcados SQL y estado de PostgreSQL</p>
        </div>
        <form action="{{ route('mantenimiento.crear-backup') }}" method="POST" id="formCrearBackup">
            @csrf
            <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-bold d-flex align-items-center gap-2 shadow-sm" id="btnBackup">
                <i class="fas fa-cloud-arrow-down"></i> <span>Generar Copia de Seguridad Ahora</span>
            </button>
        </form>
    </div>

    <!-- Widgets Informativos de la Base de Datos -->
    <div class="row g-3 mb-4">
        <!-- Motor y Versión -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted fw-bold small text-uppercase">Motor de BD</span>
                    <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2">
                        <i class="fas fa-server fs-5"></i>
                    </div>
                </div>
                <h4 class="fw-extrabold text-dark mb-1">{{ $dbInfo['motor'] }}</h4>
                <div class="d-flex align-items-center gap-1 small text-muted">
                    <i class="fas fa-code-branch text-primary"></i>
                    <span>Versión: {{ $dbInfo['version'] }}</span>
                </div>
            </div>
        </div>

        <!-- Base de Datos & Host -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted fw-bold small text-uppercase">Base de Datos</span>
                    <div class="rounded-3 bg-success bg-opacity-10 text-success p-2">
                        <i class="fas fa-cubes fs-5"></i>
                    </div>
                </div>
                <h4 class="fw-extrabold text-success mb-1 text-truncate" title="{{ $dbInfo['base_datos'] }}">{{ $dbInfo['base_datos'] }}</h4>
                <div class="d-flex align-items-center gap-1 small text-muted">
                    <i class="fas fa-network-wired"></i>
                    <span>{{ $dbInfo['host'] }}:{{ $dbInfo['puerto'] }}</span>
                </div>
            </div>
        </div>

        <!-- Peso / Tamaño Actual -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted fw-bold small text-uppercase">Tamaño en Disco</span>
                    <div class="rounded-3 bg-info bg-opacity-10 text-info p-2">
                        <i class="fas fa-hdd fs-5"></i>
                    </div>
                </div>
                <h4 class="fw-extrabold text-dark mb-1">{{ $dbInfo['tamano'] }}</h4>
                <div class="d-flex align-items-center gap-1 small text-muted">
                    <i class="fas fa-table text-info"></i>
                    <span>{{ $dbInfo['tablas'] }} tablas registradas</span>
                </div>
            </div>
        </div>

        <!-- Estado de Conexión -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted fw-bold small text-uppercase">Estado Conexión</span>
                    <div class="rounded-3 {{ $dbInfo['conectado'] ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger' }} p-2">
                        <i class="fas {{ $dbInfo['conectado'] ? 'fa-check-circle' : 'fa-times-circle' }} fs-5"></i>
                    </div>
                </div>
                <h4 class="fw-extrabold {{ $dbInfo['conectado'] ? 'text-success' : 'text-danger' }} mb-1">
                    {{ $dbInfo['conectado'] ? 'En Línea' : 'Desconectado' }}
                </h4>
                <div class="d-flex align-items-center gap-1 small {{ $dbInfo['conectado'] ? 'text-success' : 'text-danger' }}">
                    <i class="fas fa-circle" style="font-size: 0.6rem;"></i>
                    <span>{{ $dbInfo['conectado'] ? 'Servicio activo y respondiendo' : 'Verificar credenciales .env' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Copias de Seguridad Disponibles -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-transparent border-0 p-4 pb-0 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h5 class="fw-bold mb-1">Archivos de Respaldo Generados</h5>
                <p class="text-muted small mb-0">Descarga copias de seguridad de forma periódica para salvaguardar la información</p>
            </div>
            <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill">
                {{ count($backups) }} respaldos almacenados
            </span>
        </div>
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="text-muted small text-uppercase">
                            <th>Archivo de Respaldo</th>
                            <th>Fecha y Hora</th>
                            <th>Antigüedad</th>
                            <th>Tamaño</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($backups as $b)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2">
                                            <i class="fas fa-file-code fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark font-monospace small">{{ $b['nombre'] }}</div>
                                            <small class="text-muted">Formato SQL compatible con PostgreSQL</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark small">{{ $b['fecha'] }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border px-2 py-1 small">
                                        {{ $b['hace_tiempo'] }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-success bg-opacity-10 text-success fw-bold px-2 py-1">
                                        {{ $b['tamano'] }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <!-- Descargar -->
                                        <a href="{{ route('mantenimiento.descargar-backup', $b['nombre']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold shadow-xs">
                                            <i class="fas fa-download me-1"></i> Descargar
                                        </a>

                                        <!-- Eliminar -->
                                        <form action="{{ route('mantenimiento.eliminar-backup') }}" method="POST" class="d-inline formEliminarBackup">
                                            @csrf
                                            <input type="hidden" name="archivo" value="{{ $b['nombre'] }}">
                                            <button type="button" class="btn btn-sm btn-light text-danger rounded-circle p-2 btn-confirm-delete" title="Eliminar archivo">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fa-3x mb-2 opacity-40"></i>
                                    <p class="mb-0">Aún no se han generado copias de seguridad.</p>
                                    <small class="text-muted">Haz clic en "Generar Copia de Seguridad Ahora" arriba para crear la primera.</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Guía de Restauración de Copias de Seguridad -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <h6 class="fw-bold text-dark mb-2">
                <i class="fas fa-circle-question text-primary me-2"></i>¿Cómo restaurar una copia de seguridad en caso de ser necesario?
            </h6>
            <div class="row g-3 small text-muted">
                <div class="col-12 col-md-6">
                    <div class="p-3 bg-light rounded-3 h-100 border">
                        <strong class="text-dark d-block mb-1"><i class="fas fa-desktop me-1"></i> Opción 1: Mediante pgAdmin 4 (Recomendado)</strong>
                        <ol class="ps-3 mb-0">
                            <li>Abre <strong>pgAdmin 4</strong> y localiza la base de datos <code>cultura_fit</code>.</li>
                            <li>Haz clic derecho sobre la base de datos y selecciona <strong>Restore...</strong></li>
                            <li>En la pestaña <em>General</em>, busca y selecciona el archivo <code>.sql</code> descargado.</li>
                            <li>Haz clic en <strong>Restore</strong> y el esquema se restaurará de inmediato.</li>
                        </ol>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="p-3 bg-light rounded-3 h-100 border">
                        <strong class="text-dark d-block mb-1"><i class="fas fa-terminal me-1"></i> Opción 2: Mediante consola psql</strong>
                        <p class="mb-2">Abre tu terminal de comandos en Windows y ejecuta:</p>
                        <code class="d-block p-2 bg-white rounded border text-dark">
                            psql -U postgres -d cultura_fit -f "ruta\a\tu\archivo.sql"
                        </code>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const formBackup = document.getElementById('formCrearBackup');
    const btnBackup = document.getElementById('btnBackup');

    if (formBackup) {
        formBackup.addEventListener('submit', function () {
            btnBackup.disabled = true;
            btnBackup.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Generando volcado SQL...';
        });
    }

    // Confirmación para eliminar respaldos
    document.querySelectorAll('.btn-confirm-delete').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const form = this.closest('form');
            Swal.fire({
                title: '¿Eliminar este respaldo?',
                text: 'El archivo SQL se borrará del servidor de forma permanente.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
</script>
@endpush
