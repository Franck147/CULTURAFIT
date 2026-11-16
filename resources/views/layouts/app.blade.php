<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Cultura Fit') - {{ $config->nombre_sistema ?? 'CULTURA FIT' }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/gym-theme.css?v={{ time() }}">

    <style>
        :root {
            --gym-primary: #6366F1;
            --gym-primary-hover: #4F46E5;
            --gym-bg-light: #F8FAFC;
        }
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background-color: var(--gym-bg-light);
            color: #1E293B;
            overflow-x: hidden;
        }
        #sidebar-wrapper {
            width: 270px;
            min-height: 100vh;
            background: #ffffff;
            border-right: 1px solid #E2E8F0;
            transition: all 0.25s ease-out;
            position: sticky;
            top: 0;
            z-index: 1040;
        }
        .sidebar-logo-pill {
            background: #0B0F19;
            width: 100%;
            max-width: 238px;
            height: 50px;
            border-radius: 12px;
            padding: 6px 16px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.16), 0 0 0 1px rgba(255, 255, 255, 0.08);
            transition: all 0.2s ease;
        }
        .sidebar-logo-pill:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.22), 0 0 0 1px rgba(255, 255, 255, 0.12);
        }
        .sidebar-logo-img {
            max-height: 38px;
            max-width: 100%;
            width: auto;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }
        #page-content-wrapper {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .nav-link-custom {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 16px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.9rem;
            color: #64748B;
            text-decoration: none;
            transition: all 0.2s ease;
            margin-bottom: 4px;
        }
        .nav-link-custom:hover {
            background-color: #F1F5F9;
            color: var(--gym-primary);
        }
        .nav-link-custom.active {
            background: linear-gradient(135deg, var(--gym-primary), #4F46E5);
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
        }
        .nav-section-title {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #94A3B8;
            padding: 14px 16px 6px;
        }
        @media (max-width: 991.98px) {
            #sidebar-wrapper {
                margin-left: -270px;
                position: fixed;
                height: 100vh;
            }
            #wrapper.toggled #sidebar-wrapper {
                margin-left: 0;
            }
            #wrapper.toggled::after {
                content: '';
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, 0.4);
                backdrop-filter: blur(2px);
                z-index: 1030;
            }
        }
        /* DataTables length select & filter fixes */
        .dataTables_wrapper .dataTables_length label,
        .dataTables_wrapper .dataTables_filter label {
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            font-size: 0.875rem !important;
            font-weight: 500 !important;
            color: #475569 !important;
            margin-bottom: 1rem !important;
        }
        .dataTables_wrapper .dataTables_length select {
            display: inline-block !important;
            width: auto !important;
            min-width: 78px !important;
            height: 38px !important;
            padding: 0.35rem 2.2rem 0.35rem 0.85rem !important;
            font-size: 0.875rem !important;
            font-weight: 600 !important;
            line-height: 1.5 !important;
            color: #1E293B !important;
            background-color: #ffffff !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23475569' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
            background-repeat: no-repeat !important;
            background-position: right 0.7rem center !important;
            background-size: 12px 10px !important;
            border: 1.5px solid #CBD5E1 !important;
            border-radius: 10px !important;
            cursor: pointer !important;
            margin: 0 0.35rem !important;
            vertical-align: middle !important;
        }
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 10px !important;
            border: 1.5px solid #CBD5E1 !important;
            padding: 0.4rem 1rem !important;
            height: 38px !important;
            font-size: 0.875rem !important;
            color: #1E293B !important;
            min-width: 200px !important;
        }
    </style>
    @livewireStyles
    @stack('styles')
</head>
<body>

<div class="d-flex" id="wrapper">
    <!-- Sidebar -->
    <aside id="sidebar-wrapper" class="d-flex flex-column">
        <!-- Logo / Marca -->
        <div class="px-3 py-3 border-bottom position-relative d-flex align-items-center justify-content-center" style="min-height: 72px;">
            <a href="{{ route('dashboard') }}" class="d-flex align-items-center justify-content-center text-decoration-none w-100">
                @if(!empty($config->logo) && file_exists(public_path('img/' . $config->logo)))
                    <div class="sidebar-logo-pill">
                        <img src="/img/{{ $config->logo }}?v={{ time() }}" alt="{{ $config->nombre_sistema ?? 'CULTURA FIT' }}" class="sidebar-logo-img">
                    </div>
                @else
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 36px; height: 36px;">
                        <i class="fas fa-cubes fs-5"></i>
                    </div>
                    <span class="fw-extrabold fs-5 tracking-tight text-primary">{{ $config->nombre_sistema ?? 'CULTURA FIT' }}</span>
                @endif
            </a>
            <button class="btn btn-sm btn-light d-lg-none position-absolute end-0 me-3" id="close-sidebar-btn" aria-label="Cerrar menú">
                <i class="fas fa-times text-muted"></i>
            </button>
        </div>

        <!-- Usuario Resumen -->
        <div class="px-4 py-3 bg-light bg-opacity-50 border-bottom d-flex align-items-center gap-3">
            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 42px; height: 42px; font-size: 1.1rem;">
                {{ strtoupper(substr(auth()->user()->nombre ?? 'U', 0, 1)) }}
            </div>
            <div class="overflow-hidden">
                <h6 class="mb-0 fw-bold text-dark text-truncate" style="font-size: 0.9rem;">{{ auth()->user()->nombre ?? 'Usuario' }}</h6>
                <span class="badge bg-primary bg-opacity-10 text-primary text-uppercase" style="font-size: 0.68rem; font-weight: 700;">
                    {{ auth()->user()->rol ?? 'Invitado' }}
                </span>
            </div>
        </div>

        <!-- Navegación Menú -->
        <div class="p-3 overflow-y-auto flex-grow-1" style="max-height: calc(100vh - 190px);">
            <div class="nav-section-title">General</div>
            <a href="{{ route('dashboard') }}" class="nav-link-custom {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-home fa-fw"></i> Dashboard
            </a>

            <div class="nav-section-title">Comercial & Clientes</div>
            <a href="{{ route('socios.index') }}" class="nav-link-custom {{ request()->routeIs('socios.*') ? 'active' : '' }}">
                <i class="fas fa-users fa-fw"></i> Clientes
            </a>

            @if(auth()->user()->esAdmin() || auth()->user()->esCajero())
                <div class="nav-section-title">Caja & Facturación</div>
                <a href="{{ route('caja.index') }}" class="nav-link-custom {{ request()->routeIs('caja.*') ? 'active' : '' }}">
                    <i class="fas fa-cash-register fa-fw"></i> Control de Caja
                </a>
                <a href="{{ route('pos.index') }}" class="nav-link-custom {{ request()->routeIs('pos.index') ? 'active' : '' }}">
                    <i class="fas fa-shopping-cart fa-fw"></i> Punto de Venta
                </a>
                <a href="{{ route('pos.historial') }}" class="nav-link-custom {{ request()->routeIs('pos.historial') ? 'active' : '' }}">
                    <i class="fas fa-receipt fa-fw"></i> Historial de Ventas
                </a>
                <a href="{{ route('gastos.index') }}" class="nav-link-custom {{ request()->routeIs('gastos.*') ? 'active' : '' }}">
                    <i class="fas fa-money-bill-wave fa-fw"></i> Gastos y Salidas
                </a>
            @endif

            @if(auth()->user()->esAdmin())
                <div class="nav-section-title">Administración</div>
                <a href="{{ route('inventario.productos') }}" class="nav-link-custom {{ request()->routeIs('inventario.productos*') ? 'active' : '' }}">
                    <i class="fas fa-boxes fa-fw"></i> Inventario Productos
                </a>
                <a href="{{ route('inventario.categorias') }}" class="nav-link-custom {{ request()->routeIs('inventario.categorias*') ? 'active' : '' }}">
                    <i class="fas fa-tags fa-fw"></i> Categorías
                </a>
                <a href="{{ route('reportes.index') }}" class="nav-link-custom {{ request()->routeIs('reportes.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-line fa-fw"></i> Reportes Financieros
                </a>
                <a href="{{ route('usuarios.index') }}" class="nav-link-custom {{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
                    <i class="fas fa-user-shield fa-fw"></i> Usuarios del Sistema
                </a>
                <a href="{{ route('configuracion.index') }}" class="nav-link-custom {{ request()->routeIs('configuracion.*') ? 'active' : '' }}">
                    <i class="fas fa-cog fa-fw"></i> Configuración
                </a>
                <a href="{{ route('mantenimiento.index') }}" class="nav-link-custom {{ request()->routeIs('mantenimiento.*') ? 'active' : '' }}">
                    <i class="fas fa-database fa-fw"></i> Copias de Seguridad BD
                </a>
            @endif
        </div>

        <!-- Logout Bottom -->
        <div class="p-3 border-top mt-auto bg-white">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-danger w-100 rounded-pill fw-bold btn-sm py-2 d-flex align-items-center justify-content-center gap-2">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </button>
            </form>
        </div>
    </aside>

    <!-- Page Content Wrapper -->
    <div id="page-content-wrapper">
        <!-- Topbar Mobile -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-4 py-3 d-lg-none sticky-top shadow-sm">
            <button class="btn btn-light rounded-3" id="menu-toggle">
                <i class="fas fa-bars"></i>
            </button>
            <span class="fw-bold fs-6 ms-2">{{ $config->nombre_sistema ?? 'CULTURA FIT' }}</span>
            <div class="ms-auto d-flex align-items-center gap-2">
                <span class="badge bg-primary rounded-pill px-3 py-2">{{ auth()->user()->nombre ?? 'Usuario' }}</span>
            </div>
        </nav>

        <!-- Flash Messages -->
        <div class="container-fluid px-4 pt-3">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4" role="alert">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm rounded-4" role="alert">
                    <i class="fas fa-info-circle me-2"></i> {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('acceso_denegado'))
                <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm rounded-4" role="alert">
                    <i class="fas fa-shield-alt me-2"></i> {{ session('acceso_denegado') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        </div>

        <!-- Main Content Slot -->
        <main class="flex-grow-1 p-4">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-white border-top py-3 text-center text-muted small mt-auto">
            &copy; {{ date('Y') }} <strong>{{ $config->nombre_sistema ?? 'CULTURA FIT' }}</strong> - Todos los derechos reservados.
        </footer>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const wrapper = document.getElementById('wrapper');
        const menuToggle = document.getElementById('menu-toggle');
        const closeSidebar = document.getElementById('close-sidebar-btn');

        if(menuToggle) {
            menuToggle.addEventListener('click', function(e) {
                e.preventDefault();
                wrapper.classList.toggle('toggled');
            });
        }
        if(closeSidebar) {
            closeSidebar.addEventListener('click', function(e) {
                e.preventDefault();
                wrapper.classList.remove('toggled');
            });
        }

        // Suppress DataTables alert popups
        if (typeof $.fn.dataTable !== 'undefined') {
            $.fn.dataTable.ext.errMode = 'none';
        }

        // DataTable Initialization
        if ($('.table-data').length > 0) {
            $('.table-data').DataTable({
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                },
                pageLength: 10,
                responsive: true
            });

            // Auto adjust columns on Bootstrap Tab change
            $('button[data-bs-toggle="pill"], button[data-bs-toggle="tab"]').on('shown.bs.tab', function () {
                if (typeof $.fn.dataTable !== 'undefined') {
                    $($.fn.dataTable.tables(true)).DataTable().columns.adjust().responsive.recalc();
                }
            });
        }

        // Confirm buttons
        $(document).on('click', '.btn-confirm', function(e) {
            e.preventDefault();
            const form = $(this).closest('form');
            const title = $(this).data('title') || '¿Estás seguro?';
            const text = $(this).data('text') || 'Esta acción modificará los datos seleccionados.';

            Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#6366F1',
                cancelButtonColor: '#EF4444',
                confirmButtonText: 'Sí, proceder',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>

@livewireScripts
@stack('scripts')
</body>
</html>
