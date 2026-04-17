<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión — Alitas Vega</title>
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
            background: #0a0a0a;
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
            background: linear-gradient(145deg, #141414, #1a1a1a);
            border: 1px solid #2a2a2a;
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
            color: #fff;
            font-size: 1.5rem;
            font-weight: 800;
            text-align: center;
            margin-bottom: 0.25rem;
            letter-spacing: -0.02em;
        }
        .login-subtitle {
            color: #555;
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
            color: #777;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 0.4rem;
            letter-spacing: 0.02em;
        }
        .form-input {
            width: 100%;
            padding: 0.7rem 0.9rem;
            background: #0d0d0d;
            border: 1px solid #2a2a2a;
            border-radius: 12px;
            color: #fff;
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
            color: #333;
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
            color: #666;
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
            color: #fff;
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
            color: #333;
            font-size: 0.7rem;
            font-weight: 500;
            letter-spacing: 0.03em;
        }
    </style>
</head>
<body>
    <div class="login-card">

        <div class="login-icon">🍗</div>
        <h1 class="login-title">Alitas Vega</h1>
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
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    class="form-input"
                    placeholder="••••••••"
                >
            </div>

            <div class="remember-row">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Recordarme</label>
            </div>

            <button type="submit" class="btn-login">
                Ingresar
            </button>
        </form>

        <p class="login-footer">Alitas Vega © {{ date('Y') }} — Todos los derechos reservados</p>
    </div>
</body>
</html>
