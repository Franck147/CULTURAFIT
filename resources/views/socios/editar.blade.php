@extends('layouts.app')

@section('title', 'Editar Socio')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <!-- Header -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h3 class="fw-extrabold text-dark mb-1">Editar Socio: {{ $socio->nombre }}</h3>
                    <p class="text-muted small mb-0">Actualiza los datos personales, de contacto y estado del socio</p>
                </div>
                <a href="{{ route('socios.index') }}" class="btn btn-light rounded-pill px-3">
                    <i class="fas fa-arrow-left me-1"></i> Volver a Socios
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

                    <form action="{{ route('socios.actualizar', $socio->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Foto de Perfil Preview -->
                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block">
                                @if($socio->foto && file_exists(public_path('img/socios/' . $socio->foto)))
                                    <img id="fotoPreview" src="/img/socios/{{ $socio->foto }}" alt="{{ $socio->nombre }}" class="rounded-circle object-fit-cover shadow-sm border" width="120" height="120">
                                @else
                                    <img id="fotoPreview" src="https://via.placeholder.com/120?text=Foto" alt="Preview" class="rounded-circle object-fit-cover shadow-sm border" width="120" height="120">
                                @endif
                                <label for="fotoInput" class="btn btn-sm btn-primary rounded-circle position-absolute bottom-0 end-0 p-2 shadow" style="cursor: pointer;" title="Cambiar Foto">
                                    <i class="fas fa-camera"></i>
                                </label>
                                <input type="file" name="foto" id="fotoInput" class="d-none" accept="image/jpeg,image/png,image/webp" onchange="previewImage(event)">
                            </div>
                            <div class="small text-muted mt-2">Haz clic en la cámara para subir una nueva foto</div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-secondary mb-1">Nombre Completo <span class="text-danger">*</span></label>
                                <input type="text" name="nombre" class="form-control rounded-3" value="{{ old('nombre', $socio->nombre) }}" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-secondary mb-1">DNI / Documento de Identidad <span class="text-danger">*</span></label>
                                <input type="text" name="dni" class="form-control rounded-3" value="{{ old('dni', $socio->dni) }}" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-secondary mb-1">Correo Electrónico</label>
                                <input type="email" name="email" class="form-control rounded-3" value="{{ old('email', $socio->email) }}">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-secondary mb-1">Teléfono / WhatsApp</label>
                                <input type="text" name="telefono" class="form-control rounded-3" value="{{ old('telefono', $socio->telefono) }}">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-secondary mb-1">Estado de Membresía <span class="text-danger">*</span></label>
                                <select name="estado" class="form-select rounded-3" required>
                                    <option value="activo" {{ old('estado', $socio->estado) == 'activo' ? 'selected' : '' }}>Activo</option>
                                    <option value="inactivo" {{ old('estado', $socio->estado) == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                                    <option value="pendiente" {{ old('estado', $socio->estado) == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-5">
                            <a href="{{ route('socios.index') }}" class="btn btn-light rounded-pill px-4">Cancelar</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                                <i class="fas fa-save me-1"></i> Actualizar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(event) {
    const input = event.target;
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('fotoPreview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
