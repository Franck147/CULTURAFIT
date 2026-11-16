<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - {{ $config->nombre_sistema ?? 'CULTURA FIT' }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary: #6366F1;
            --primary-hover: #4F46E5;
            --primary-dark: #312E81;
            --dark-surface: #0B0F19;
            --text-dark: #0F172A;
            --text-muted: #64748B;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background-color: #F8FAFC;
            color: var(--text-dark);
            min-height: 100vh;
            margin: 0;
            overflow-x: hidden;
        }

        .split-layout {
            min-height: 100vh;
            display: flex;
        }

        /* ---------------- Visual Panel (Left) - Solo Fondo ---------------- */
        .visual-panel {
            flex: 1.25;
            position: relative;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.3) 0%, rgba(30, 27, 75, 0.2) 50%, rgba(15, 23, 42, 0.45) 100%),
                        url('/img/gym-login-bg.jpg') center/cover no-repeat;
            min-height: 100vh;
        }

        /* ---------------- Form Panel (Right) ---------------- */
        .form-panel {
            flex: 0.95;
            background-color: #FFFFFF;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 3rem 2rem;
            position: relative;
            min-height: 100vh;
        }

        .form-container {
            width: 100%;
            max-width: 420px;
        }

        /* Logo badge inside form (Centered) */
        .form-logo-badge {
            background: #0B0F19;
            padding: 12px 28px;
            border-radius: 20px;
            display: inline-block;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.22), 0 0 0 1px rgba(255, 255, 255, 0.08);
            margin-bottom: 1.5rem;
            transition: transform 0.25s ease;
        }

        .form-logo-badge:hover {
            transform: translateY(-2px);
        }

        .form-logo-img {
            max-height: 48px;
            width: auto;
            object-fit: contain;
            display: block;
        }

        .form-title {
            font-size: 1.65rem;
            font-weight: 800;
            color: #0F172A;
            letter-spacing: -0.02em;
            margin-bottom: 0.35rem;
        }

        .form-subtitle {
            font-size: 0.9rem;
            color: #64748B;
            margin-bottom: 2rem;
        }

        .form-label {
            font-size: 0.84rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.4rem;
        }

        .input-group-custom {
            position: relative;
            margin-bottom: 1.25rem;
        }

        .input-group-custom .form-control {
            height: 48px;
            border-radius: 12px;
            border: 1.5px solid #E2E8F0;
            padding-left: 2.75rem;
            font-size: 0.92rem;
            font-weight: 500;
            color: #0F172A;
            background-color: #F8FAFC;
            transition: all 0.2s ease;
        }

        .input-group-custom .form-control:focus {
            background-color: #FFFFFF;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12);
        }

        .input-group-custom .input-icon-left {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
            font-size: 1rem;
            z-index: 4;
            pointer-events: none;
            transition: color 0.2s ease;
        }

        .input-group-custom .form-control:focus ~ .input-icon-left,
        .input-group-custom:focus-within .input-icon-left {
            color: var(--primary);
        }

        .btn-toggle-eye {
            position: absolute;
            right: 0.85rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94A3B8;
            padding: 6px;
            cursor: pointer;
            z-index: 5;
            transition: color 0.2s ease;
        }

        .btn-toggle-eye:hover {
            color: var(--text-dark);
        }

        .btn-submit {
            height: 50px;
            background: linear-gradient(135deg, #6366F1 0%, #4F46E5 100%);
            border: none;
            border-radius: 12px;
            color: #FFFFFF;
            font-size: 0.96rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 8px 20px -4px rgba(99, 102, 241, 0.4);
            transition: all 0.25s ease;
            width: 100%;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px -4px rgba(99, 102, 241, 0.5);
            color: #FFFFFF;
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        /* Demo access section */
        .demo-section {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #E2E8F0;
            text-align: center;
        }

        .demo-title {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #94A3B8;
            margin-bottom: 0.75rem;
        }

        .demo-pills-row {
            display: flex;
            gap: 8px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .demo-pill {
            background-color: #F1F5F9;
            border: 1px solid #E2E8F0;
            border-radius: 10px;
            padding: 8px 14px;
            font-size: 0.78rem;
            font-weight: 600;
            color: #334155;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .demo-pill:hover {
            background-color: #E0E7FF;
            border-color: #C7D2FE;
            color: #3730A3;
            transform: translateY(-1px);
        }

        /* Mobile Responsive */
        @media (max-width: 991.98px) {
            .split-layout {
                flex-direction: column;
            }
            .visual-panel {
                display: none;
            }
            .form-panel {
                flex: 1;
                min-height: 100vh;
                padding: 2.5rem 1.5rem;
            }
        }
    </style>
</head>
<body>

<div class="split-layout">
    <!-- Visual Left Panel: Solo Fondo Cinematográfico Limpio -->
    <div class="visual-panel"></div>

    <!-- Form Right Panel -->
    <div class="form-panel">
        <div class="form-container">
            <!-- Logo inside form (Centered) -->
            <div class="text-center mb-4">
                <div class="form-logo-badge mx-auto">
                    @if(!empty($config->logo) && file_exists(public_path('img/' . $config->logo)))
                        <img src="/img/{{ $config->logo }}?v={{ time() }}" alt="{{ $config->nombre_sistema ?? 'CULTURA FIT' }}" class="form-logo-img">
                    @else
                        <div class="d-flex align-items-center justify-content-center gap-2 text-white fw-bold">
                            <i class="fas fa-cubes text-primary fs-5"></i>
                            <span class="fs-5">{{ $config->nombre_sistema ?? 'CULTURA FIT' }}</span>
                        </div>
                    @endif
                </div>

                <h2 class="form-title">Bienvenido de nuevo</h2>
                <p class="form-subtitle">Ingresa tus credenciales para administrar la tienda</p>
            </div>

            <!-- Error Alerts -->
            @if($errors->any())
                <div class="alert alert-danger border-0 rounded-4 py-3 px-3 mb-4 shadow-sm" role="alert">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-exclamation-circle text-danger fs-5"></i>
                        <span class="small fw-semibold">{{ $errors->first() }}</span>
                    </div>
                </div>
            @endif

            <!-- Form -->
            <form action="{{ route('login.post') }}" method="POST">
                @csrf

                <!-- Email Input -->
                <div class="mb-3">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <div class="input-group-custom">
                        <input type="email" name="email" id="email" class="form-control" placeholder="admin@culturafit.com" value="{{ old('email') }}" required autofocus autocomplete="username">
                        <i class="fas fa-envelope input-icon-left"></i>
                    </div>
                </div>

                <!-- Password Input -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label for="password" class="form-label mb-0">Contraseña</label>
                    </div>
                    <div class="input-group-custom">
                        <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required autocomplete="current-password">
                        <i class="fas fa-lock input-icon-left"></i>
                        <button type="button" class="btn-toggle-eye" id="togglePassword" aria-label="Mostrar contraseña">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" checked>
                        <label class="form-check-label small text-muted user-select-none" for="remember">
                            Mantener sesión iniciada
                        </label>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-submit">
                    <span>Ingresar al Sistema</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <!-- Quick Demo Credentials -->
            <div class="demo-section">
                <div class="demo-title">Acceso rápido para demostración</div>
                <div class="demo-pills-row">
                    <button type="button" class="demo-pill" onclick="fillCreds('admin@gym.com', 'admin123')">
                        <i class="fas fa-user-shield text-primary"></i>
                        <span>Admin</span>
                    </button>
                    <button type="button" class="demo-pill" onclick="fillCreds('cajero@gym.com', 'cajero123')">
                        <i class="fas fa-cash-register text-success"></i>
                        <span>Cajero</span>
                    </button>
                </div>
            </div>

            <!-- Footer copyright -->
            <div class="text-center text-muted small mt-4 pt-2">
                &copy; {{ date('Y') }} <strong>{{ $config->nombre_sistema ?? 'CULTURA FIT' }}</strong>. Todos los derechos reservados.
            </div>
        </div>
    </div>
</div>

<script>
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');

    if (togglePassword && password && eyeIcon) {
        togglePassword.addEventListener('click', function () {
            const isPassword = password.getAttribute('type') === 'password';
            password.setAttribute('type', isPassword ? 'text' : 'password');
            eyeIcon.classList.toggle('fa-eye', !isPassword);
            eyeIcon.classList.toggle('fa-eye-slash', isPassword);
        });
    }

    function fillCreds(u, p) {
        document.getElementById('email').value = u;
        document.getElementById('password').value = p;
    }
</script>

</body>
</html>
