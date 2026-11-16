@extends('layouts.app')

@section('title', 'Progreso & Rutina - ' . $socio->nombre)

@section('content')
<div class="container-fluid">
    <!-- Socio Card Header -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    @if($socio->foto && file_exists(public_path('img/socios/' . $socio->foto)))
                        <img src="/img/socios/{{ $socio->foto }}" alt="{{ $socio->nombre }}" class="rounded-circle object-fit-cover shadow-sm" width="64" height="64">
                    @else
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-extrabold d-flex align-items-center justify-content-center fs-4" style="width: 64px; height: 64px;">
                            {{ strtoupper(substr($socio->nombre, 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h4 class="fw-extrabold text-dark mb-0">{{ $socio->nombre }}</h4>
                            <span class="badge rounded-pill {{ $socio->estado === 'activo' ? 'bg-success' : 'bg-secondary' }} px-3 py-1">
                                {{ ucfirst($socio->estado) }}
                            </span>
                        </div>
                        <div class="d-flex flex-wrap gap-3 small text-muted">
                            <span><i class="fas fa-id-card me-1"></i> DNI: <strong>{{ $socio->dni }}</strong></span>
                            @if($socio->telefono)
                                <span><i class="fas fa-phone-alt me-1"></i> {{ $socio->telefono }}</span>
                            @endif
                            @if($socio->email)
                                <span><i class="fas fa-envelope me-1"></i> {{ $socio->email }}</span>
                            @endif
                            <span><i class="fas fa-calendar-alt me-1"></i> Registro: {{ $socio->fecha_registro ? $socio->fecha_registro->format('d/m/Y') : '-' }}</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('socios.index') }}" class="btn btn-light rounded-pill px-3">
                        <i class="fas fa-arrow-left me-1"></i> Volver a Socios
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Pestañas de Navegación -->
    <ul class="nav nav-pills mb-4 gap-2" id="progresoTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4 py-2 fw-bold {{ request('tab') !== 'rutina' ? 'active' : '' }}" id="medidas-tab" data-bs-toggle="pill" data-bs-target="#medidasPane" type="button" role="tab">
                <i class="fas fa-weight me-2"></i> Evolución y Medidas Antropométricas
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4 py-2 fw-bold {{ request('tab') === 'rutina' ? 'active' : '' }}" id="rutina-tab" data-bs-toggle="pill" data-bs-target="#rutinaPane" type="button" role="tab">
                <i class="fas fa-dumbbell me-2"></i> Rutina Semanal Asignada
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="progresoTabContent">
        <!-- TAB 1: MEDIDAS & GRÁFICO -->
        <div class="tab-pane fade {{ request('tab') !== 'rutina' ? 'show active' : '' }}" id="medidasPane" role="tabpanel">
            <div class="row g-4">
                <!-- Gráfico de Progreso -->
                <div class="col-12 col-xl-8">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-transparent border-0 p-4 pb-0 d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="fw-bold mb-1">Curva de Transformación Física</h5>
                                <span class="text-muted small">Evolución de peso corporal y porcentaje graso en el tiempo</span>
                            </div>
                            <button class="btn btn-sm btn-primary rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalNuevaMedida">
                                <i class="fas fa-plus me-1"></i> Nueva Medida
                            </button>
                        </div>
                        <div class="card-body p-4">
                            <div style="height: 320px; position: relative;">
                                @if(count($chartLabels) > 0)
                                    <canvas id="progressChart"></canvas>
                                @else
                                    <div class="h-100 d-flex flex-column align-items-center justify-content-center text-muted">
                                        <i class="fas fa-chart-line fa-3x mb-2 opacity-40"></i>
                                        <p class="mb-0">No hay mediciones registradas para este socio.</p>
                                        <button class="btn btn-sm btn-primary rounded-pill mt-3 px-3" data-bs-toggle="modal" data-bs-target="#modalNuevaMedida">
                                            Registrar primera medida
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Historial de Medidas -->
                <div class="col-12 col-xl-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-transparent border-0 p-4 pb-0">
                            <h5 class="fw-bold mb-1">Historial de Registros</h5>
                            <span class="text-muted small">{{ count($medidas) }} mediciones tomadas</span>
                        </div>
                        <div class="card-body p-4 overflow-y-auto" style="max-height: 380px;">
                            <div class="list-group list-group-flush">
                                @forelse($medidas as $m)
                                    <div class="list-group-item px-0 py-3 border-bottom d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-bold text-dark">{{ $m->fecha->format('d/m/Y') }}</div>
                                            <div class="small text-muted">
                                                <span>Peso: <strong>{{ $m->peso }} kg</strong></span>
                                                @if($m->grasa) | <span>Grasa: <strong>{{ $m->grasa }}%</strong></span> @endif
                                                @if($m->cintura) | <span>Cintura: <strong>{{ $m->cintura }}cm</strong></span> @endif
                                                @if($m->brazo) | <span>Brazo: <strong>{{ $m->brazo }}cm</strong></span> @endif
                                            </div>
                                        </div>
                                        <form action="{{ route('progreso.eliminar-medida', $m->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="button" class="btn btn-sm btn-light text-danger rounded-circle p-2 btn-confirm" data-title="¿Eliminar medición?" data-text="Se borrará el registro del {{ $m->fecha->format('d/m/Y') }}">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                @empty
                                    <div class="text-center py-4 text-muted">
                                        <small>Sin registros previos.</small>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: RUTINA SEMANAL -->
        <div class="tab-pane fade {{ request('tab') === 'rutina' ? 'show active' : '' }}" id="rutinaPane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-0 p-4 pb-0 d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <h5 class="fw-bold mb-1">Plan de Entrenamiento Personalizado</h5>
                        <span class="text-muted small">
                            @if($rutina && $rutina->fecha_asignacion)
                                Última asignación: {{ $rutina->fecha_asignacion->format('d/m/Y H:i') }}
                            @else
                                Sin rutina asignada recientemente
                            @endif
                        </span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('progreso.guardar-rutina') }}" method="POST">
                        @csrf
                        <input type="hidden" name="socio_id" value="{{ $socio->id }}">

                        <div class="row g-3">
                            <!-- Día 1 -->
                            <div class="col-12 col-md-6 col-xl-4">
                                <div class="p-3 bg-light rounded-4 h-100 border">
                                    <label class="fw-bold text-primary mb-2 d-flex align-items-center gap-2">
                                        <i class="fas fa-calendar-day"></i> Día 1 - Lunes
                                    </label>
                                    <textarea name="dia1" class="form-control bg-white rounded-3" rows="4" placeholder="Ej: Pecho y Tríceps...&#10;Press Banca: 4x10&#10;Fondos: 3x12">{{ old('dia1', $rutina->dia1 ?? '') }}</textarea>
                                </div>
                            </div>

                            <!-- Día 2 -->
                            <div class="col-12 col-md-6 col-xl-4">
                                <div class="p-3 bg-light rounded-4 h-100 border">
                                    <label class="fw-bold text-primary mb-2 d-flex align-items-center gap-2">
                                        <i class="fas fa-calendar-day"></i> Día 2 - Martes
                                    </label>
                                    <textarea name="dia2" class="form-control bg-white rounded-3" rows="4" placeholder="Ej: Espalda y Bíceps...&#10;Dominadas: 4x8&#10;Remo con barra: 4x10">{{ old('dia2', $rutina->dia2 ?? '') }}</textarea>
                                </div>
                            </div>

                            <!-- Día 3 -->
                            <div class="col-12 col-md-6 col-xl-4">
                                <div class="p-3 bg-light rounded-4 h-100 border">
                                    <label class="fw-bold text-primary mb-2 d-flex align-items-center gap-2">
                                        <i class="fas fa-calendar-day"></i> Día 3 - Miércoles
                                    </label>
                                    <textarea name="dia3" class="form-control bg-white rounded-3" rows="4" placeholder="Ej: Piernas & Core...&#10;Sentadillas: 5x8&#10;Prensa 45°: 4x12">{{ old('dia3', $rutina->dia3 ?? '') }}</textarea>
                                </div>
                            </div>

                            <!-- Día 4 -->
                            <div class="col-12 col-md-6 col-xl-4">
                                <div class="p-3 bg-light rounded-4 h-100 border">
                                    <label class="fw-bold text-primary mb-2 d-flex align-items-center gap-2">
                                        <i class="fas fa-calendar-day"></i> Día 4 - Jueves
                                    </label>
                                    <textarea name="dia4" class="form-control bg-white rounded-3" rows="4" placeholder="Ej: Hombros y Trapecio...&#10;Press Militar: 4x10&#10;Elevaciones Laterales: 4x15">{{ old('dia4', $rutina->dia4 ?? '') }}</textarea>
                                </div>
                            </div>

                            <!-- Día 5 -->
                            <div class="col-12 col-md-6 col-xl-4">
                                <div class="p-3 bg-light rounded-4 h-100 border">
                                    <label class="fw-bold text-primary mb-2 d-flex align-items-center gap-2">
                                        <i class="fas fa-calendar-day"></i> Día 5 - Viernes
                                    </label>
                                    <textarea name="dia5" class="form-control bg-white rounded-3" rows="4" placeholder="Ej: Brazo Completo / Hipertrofia...&#10;Curl Barra: 4x10&#10;Press Francés: 4x10">{{ old('dia5', $rutina->dia5 ?? '') }}</textarea>
                                </div>
                            </div>

                            <!-- Día 6 -->
                            <div class="col-12 col-md-6 col-xl-4">
                                <div class="p-3 bg-light rounded-4 h-100 border">
                                    <label class="fw-bold text-primary mb-2 d-flex align-items-center gap-2">
                                        <i class="fas fa-calendar-day"></i> Día 6 - Sábado
                                    </label>
                                    <textarea name="dia6" class="form-control bg-white rounded-3" rows="4" placeholder="Ej: Cardio HIIT & Recuperación Activa">{{ old('dia6', $rutina->dia6 ?? '') }}</textarea>
                                </div>
                            </div>

                            <!-- Observaciones / Indicaciones Especiales -->
                            <div class="col-12">
                                <label class="form-label small fw-bold text-secondary mb-1">Notas, Objetivos & Recomendaciones Nutricionales</label>
                                <textarea name="observaciones" class="form-control rounded-3" rows="3" placeholder="Recomendaciones de suplementación, hidratación, descansos...">{{ old('observaciones', $rutina->observaciones ?? '') }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                                <i class="fas fa-save me-1"></i> Guardar Plan de Entrenamiento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nueva Medida -->
<div class="modal fade" id="modalNuevaMedida" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Registrar Medidas Antropométricas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('progreso.guardar-medida') }}" method="POST">
                @csrf
                <input type="hidden" name="socio_id" value="{{ $socio->id }}">

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary mb-1">Fecha de Medición <span class="text-danger">*</span></label>
                        <input type="date" name="fecha" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary mb-1">Peso (kg) <span class="text-danger">*</span></label>
                            <input type="number" step="0.1" min="20" max="300" name="peso" class="form-control rounded-3" placeholder="75.5" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary mb-1">Grasa Corporal (%)</label>
                            <input type="number" step="0.1" min="0" max="100" name="grasa" class="form-control rounded-3" placeholder="18.2">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary mb-1">Cintura (cm)</label>
                            <input type="number" step="0.1" min="20" max="250" name="cintura" class="form-control rounded-3" placeholder="82.0">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary mb-1">Brazo (cm)</label>
                            <input type="number" step="0.1" min="10" max="100" name="brazo" class="form-control rounded-3" placeholder="36.5">
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                        <i class="fas fa-save me-1"></i> Guardar Medición
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('progressChart');
    if (ctx && {!! count($chartLabels) !!} > 0) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [
                    {
                        label: 'Peso (kg)',
                        data: {!! json_encode($dataPeso) !!},
                        borderColor: '#6366F1',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        borderWidth: 3,
                        tension: 0.3,
                        pointRadius: 5
                    },
                    {
                        label: 'Grasa (%)',
                        data: {!! json_encode($dataGrasa) !!},
                        borderColor: '#EF4444',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        tension: 0.3,
                        pointRadius: 4
                    },
                    {
                        label: 'Cintura (cm)',
                        data: {!! json_encode($dataCintura) !!},
                        borderColor: '#F59E0B',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        tension: 0.3,
                        pointRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' }
                },
                scales: {
                    y: { beginAtZero: false, grid: { color: '#F1F5F9' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }
});
</script>
@endpush
