@extends('layouts.app')

@section('title', 'Configuración de Empresa')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <!-- Header -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h3 class="fw-extrabold text-dark mb-1">Configuración del Gimnasio</h3>
                    <p class="text-muted small mb-0">Datos de la empresa, identidad visual y parámetros de comprobantes</p>
                </div>
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

                    <form action="{{ route('configuracion.actualizar') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Logo Preview & Upload -->
                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block">
                                @if(!empty($config->logo) && file_exists(public_path('img/' . $config->logo)))
                                    <img id="logoPreview" src="/img/{{ $config->logo }}?v={{ time() }}" alt="Logo" class="rounded-circle object-fit-contain shadow-sm border p-1" width="110" height="110">
                                @else
                                    <img id="logoPreview" src="https://via.placeholder.com/110?text=Logo" alt="Preview" class="rounded-circle object-fit-contain shadow-sm border" width="110" height="110">
                                @endif
                                <label for="logoInput" class="btn btn-sm btn-primary rounded-circle position-absolute bottom-0 end-0 p-2 shadow" style="cursor: pointer;" title="Cambiar Logo">
                                    <i class="fas fa-camera"></i>
                                </label>
                                <input type="file" name="logo" id="logoInput" class="d-none" accept="image/jpeg,image/png,image/webp" onchange="previewLogo(event)">
                            </div>
                            <div class="small text-muted mt-2">Logo comercial para comprobantes y barra superior</div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-8">
                                <label class="form-label small fw-bold text-secondary mb-1">Nombre Comercial del Gimnasio <span class="text-danger">*</span></label>
                                <input type="text" name="nombre_sistema" class="form-control rounded-3" value="{{ old('nombre_sistema', $config->nombre_sistema ?? 'CULTURA FIT') }}" required>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-bold text-secondary mb-1">Símbolo de Moneda <span class="text-danger">*</span></label>
                                <input type="text" name="moneda" class="form-control rounded-3" value="{{ old('moneda', $config->moneda ?? 'S/') }}" placeholder="S/, $, €" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-secondary mb-1">RUC / NIT / CIF</label>
                                <input type="text" name="ruc" class="form-control rounded-3" value="{{ old('ruc', $config->ruc ?? '') }}" placeholder="20601234567">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-secondary mb-1">Teléfono / Celular de Contacto</label>
                                <input type="text" name="telefono" class="form-control rounded-3" value="{{ old('telefono', $config->telefono ?? '') }}" placeholder="+51 987 654 321">
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold text-secondary mb-1">Dirección del Local</label>
                                <input type="text" name="direccion" class="form-control rounded-3" value="{{ old('direccion', $config->direccion ?? '') }}" placeholder="Av. Principal 123, Distrito, Ciudad">
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold text-secondary mb-1">Correo Electrónico Institucional</label>
                                <input type="email" name="email" class="form-control rounded-3" value="{{ old('email', $config->email ?? '') }}" placeholder="contacto@culturafit.com">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-5">
                            <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                                <i class="fas fa-save me-1"></i> Guardar Configuración
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewLogo(event) {
    const input = event.target;
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('logoPreview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
