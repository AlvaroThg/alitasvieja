<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión — Alitas Vega</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div style="min-height:100vh;display:flex;align-items:center;justify-content:center;background:#111;">
        <div style="background:#1a1a1a;border:1px solid #2a2a2a;border-radius:12px;padding:2.5rem;width:100%;max-width:400px;">

            <h1 style="color:#fff;font-size:1.5rem;font-weight:700;margin-bottom:0.25rem;text-align:center;">
                🍗 Alitas Vega
            </h1>
            <p style="color:#888;font-size:0.85rem;text-align:center;margin-bottom:2rem;">
                Sistema POS — Acceso interno
            </p>

            @if ($errors->any())
                <div style="background:#3b1111;border:1px solid #7f1d1d;border-radius:8px;padding:0.75rem 1rem;margin-bottom:1.25rem;">
                    @foreach ($errors->all() as $error)
                        <p style="color:#fca5a5;font-size:0.875rem;margin:0;">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div style="margin-bottom:1rem;">
                    <label for="email" style="display:block;color:#aaa;font-size:0.8rem;margin-bottom:0.4rem;">
                        Correo electrónico
                    </label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        style="width:100%;padding:0.65rem 0.875rem;background:#111;border:1px solid #333;border-radius:8px;color:#fff;font-size:0.95rem;box-sizing:border-box;"
                    >
                </div>

                <div style="margin-bottom:1.5rem;">
                    <label for="password" style="display:block;color:#aaa;font-size:0.8rem;margin-bottom:0.4rem;">
                        Contraseña
                    </label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        style="width:100%;padding:0.65rem 0.875rem;background:#111;border:1px solid #333;border-radius:8px;color:#fff;font-size:0.95rem;box-sizing:border-box;"
                    >
                </div>

                <div style="margin-bottom:1.5rem;display:flex;align-items:center;gap:0.5rem;">
                    <input type="checkbox" id="remember" name="remember" style="accent-color:#f97316;">
                    <label for="remember" style="color:#aaa;font-size:0.85rem;cursor:pointer;">
                        Recordarme
                    </label>
                </div>

                <button
                    type="submit"
                    style="width:100%;padding:0.75rem;background:#f97316;border:none;border-radius:8px;color:#fff;font-size:1rem;font-weight:600;cursor:pointer;"
                >
                    Ingresar
                </button>
            </form>

        </div>
    </div>
</body>
</html>
