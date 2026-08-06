<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión — Alitas La Vieja</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-base);
            position: relative;
            overflow: hidden;
        }
        body::before {
            content: '';
            position: absolute;
            top: -30%;
            right: -20%;
            width: 60%;
            height: 80%;
            background: radial-gradient(circle, rgba(220, 38, 38, 0.06) 0%, transparent 70%);
            pointer-events: none;
        }
        body::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -15%;
            width: 50%;
            height: 70%;
            background: radial-gradient(circle, rgba(249, 115, 22, 0.04) 0%, transparent 70%);
            pointer-events: none;
        }
        .login-card {
            background: linear-gradient(145deg, var(--bg-surface), var(--bg-elevated));
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 1;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
        }
        .login-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, #dc2626, #f97316, #dc2626);
            border-radius: 24px 24px 0 0;
        }
        .login-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
            font-size: 1.75rem;
            box-shadow: 0 8px 24px rgba(220, 38, 38, 0.2);
        }
        .login-title {
            color: var(--text-strong);
            font-size: 1.5rem;
            font-weight: 800;
            text-align: center;
            margin-bottom: 0.25rem;
            letter-spacing: -0.02em;
        }
        .login-subtitle {
            color: var(--text-faint);
            font-size: 0.85rem;
            text-align: center;
            margin-bottom: 2rem;
            font-weight: 500;
        }
        /* Error box */
        .login-error {
            background: rgba(220, 38, 38, 0.08);
            border: 1px solid rgba(220, 38, 38, 0.2);
            border-radius: 12px;
            padding: 0.75rem 1rem;
            margin-bottom: 1.25rem;
        }
        .login-error p {
            color: #f87171;
            font-size: 0.8rem;
            margin: 0;
        }
        /* Form */
        .form-group {
            margin-bottom: 1.15rem;
        }
        .form-label {
            display: block;
            color: var(--text-muted);
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 0.4rem;
            letter-spacing: 0.02em;
        }
        .form-input {
            width: 100%;
            padding: 0.7rem 0.9rem;
            background: var(--bg-base);
            border: 1px solid var(--border);
            border-radius: 12px;
            color: var(--text-strong);
            font-size: 0.9rem;
            font-family: inherit;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .form-input:focus {
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.08);
        }
        .form-input::placeholder {
            color: var(--border-strong);
        }
        /* Campo de contraseña con botón de ver/ocultar */
        .password-wrap {
            position: relative;
        }
        .password-wrap .form-input {
            padding-right: 2.9rem; /* espacio para que el texto no pase por debajo del ojito */
        }
        .btn-eye {
            position: absolute;
            top: 50%;
            right: 0.55rem;
            transform: translateY(-50%);
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            padding: 0;
            background: transparent;
            border: none;
            border-radius: 8px;
            color: var(--text-muted);
            cursor: pointer;
            transition: color 0.2s ease, background 0.2s ease;
        }
        .btn-eye:hover {
            color: var(--text-strong);
            background: var(--bg-elevated);
        }
        .btn-eye:focus-visible {
            outline: 2px solid #dc2626;
            outline-offset: 1px;
        }
        /* Remember */
        .remember-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .remember-row input[type="checkbox"] {
            accent-color: #dc2626;
            width: 15px;
            height: 15px;
        }
        .remember-row label {
            color: var(--text-muted);
            font-size: 0.8rem;
            cursor: pointer;
            font-weight: 500;
        }
        /* Submit */
        .btn-login {
            width: 100%;
            padding: 0.85rem;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            border: none;
            border-radius: 14px;
            color: var(--text-strong);
            font-size: 0.95rem;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(220, 38, 38, 0.2);
            letter-spacing: 0.02em;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(220, 38, 38, 0.3);
        }
        .btn-login:active {
            transform: scale(0.97);
        }
        /* Footer */
        .login-footer {
            text-align: center;
            margin-top: 2rem;
            color: var(--border-strong);
            font-size: 0.7rem;
            font-weight: 500;
            letter-spacing: 0.03em;
        }
    </style>
</head>
<body>
    <div class="login-card">

        <div class="login-icon">🍗</div>
        <h1 class="login-title">Alitas La Vieja</h1>
        <p class="login-subtitle">Sistema POS — Acceso interno</p>

        @if ($errors->any())
            <div class="login-error">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label for="email" class="form-label">
                    Correo electrónico
                </label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    class="form-input"
                    placeholder="admin@alitasvega.com"
                >
            </div>

            <div class="form-group">
                <label for="password" class="form-label">
                    Contraseña
                </label>
                <div class="password-wrap">
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        class="form-input"
                        placeholder="••••••••"
                    >
                    <button type="button" id="toggle-password" class="btn-eye"
                            aria-label="Mostrar contraseña" aria-pressed="false" title="Mostrar contraseña">
                        {{-- Ojo abierto: la contraseña está oculta --}}
                        <svg id="eye-show" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        {{-- Ojo tachado: la contraseña está visible --}}
                        <svg id="eye-hide" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="remember-row">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Recordarme</label>
            </div>

            <button type="submit" class="btn-login">
                Ingresar
            </button>
        </form>

        <p class="login-footer">Alitas La Vieja © {{ date('Y') }} — Todos los derechos reservados</p>
    </div>

    {{-- Ver / ocultar la contraseña. En JS plano: el login no carga Livewire
         (y con él Alpine), así que no se puede depender de x-on. --}}
    <script>
        (function () {
            var input  = document.getElementById('password');
            var boton  = document.getElementById('toggle-password');
            var abierto = document.getElementById('eye-show');
            var tachado = document.getElementById('eye-hide');
            if (!input || !boton) return;

            boton.addEventListener('click', function () {
                var visible = input.type === 'text';

                input.type = visible ? 'password' : 'text';
                abierto.style.display = visible ? '' : 'none';
                tachado.style.display = visible ? 'none' : '';

                var etiqueta = visible ? 'Mostrar contraseña' : 'Ocultar contraseña';
                boton.setAttribute('aria-label', etiqueta);
                boton.setAttribute('title', etiqueta);
                boton.setAttribute('aria-pressed', visible ? 'false' : 'true');

                // Al alternar, el cursor se va al inicio: se devuelve al final.
                input.focus();
                var fin = input.value.length;
                try { input.setSelectionRange(fin, fin); } catch (e) {}
            });
        })();
    </script>
</body>
</html>
