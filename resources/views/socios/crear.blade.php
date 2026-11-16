@extends('layouts.app')

@section('title', 'Nuevo Socio')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <!-- Header -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h3 class="fw-extrabold text-dark mb-1">Registrar Nuevo Socio</h3>
                    <p class="text-muted small mb-0">Crea el perfil del cliente para registrar ventas y planes de entrenamiento</p>
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

                    <form action="{{ route('socios.guardar') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Foto de Perfil Preview -->
                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block">
                                <img id="fotoPreview" src="https://via.placeholder.com/120?text=Foto" alt="Preview" class="rounded-circle object-fit-cover shadow-sm border" width="120" height="120">
                                <label for="fotoInput" class="btn btn-sm btn-primary rounded-circle position-absolute bottom-0 end-0 p-2 shadow" style="cursor: pointer;" title="Subir Foto">
                                    <i class="fas fa-camera"></i>
                                </label>
                                <input type="file" name="foto" id="fotoInput" class="d-none" accept="image/jpeg,image/png,image/webp" onchange="previewImage(event)">
                            </div>
                            <div class="small text-muted mt-2">Formatos válidos: JPG, PNG, WEBP (Máx. 2MB)</div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-secondary mb-1">Nombre Completo <span class="text-danger">*</span></label>
                                <input type="text" name="nombre" class="form-control rounded-3" value="{{ old('nombre') }}" placeholder="Ej: Carlos Mendoza" required autofocus>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-secondary mb-1">DNI / Documento de Identidad <span class="text-danger">*</span></label>
                                <input type="text" name="dni" class="form-control rounded-3" value="{{ old('dni') }}" placeholder="Ej: 74839201" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-secondary mb-1">Correo Electrónico</label>
                                <input type="email" name="email" class="form-control rounded-3" value="{{ old('email') }}" placeholder="ejemplo@correo.com">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-secondary mb-1">Teléfono / WhatsApp</label>
                                <input type="text" name="telefono" class="form-control rounded-3" value="{{ old('telefono') }}" placeholder="Ej: 987654321">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-secondary mb-1">Estado de Membresía <span class="text-danger">*</span></label>
                                <select name="estado" class="form-select rounded-3" required>
                                    <option value="activo" {{ old('estado') == 'activo' ? 'selected' : '' }}>Activo</option>
                                    <option value="inactivo" {{ old('estado') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                                    <option value="pendiente" {{ old('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-5">
                            <a href="{{ route('socios.index') }}" class="btn btn-light rounded-pill px-4">Cancelar</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                                <i class="fas fa-save me-1"></i> Guardar Socio
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
