<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alitas La Vieja — Sistema de Punto de Venta</title>
    <meta name="description" content="Plataforma interna de Alitas La Vieja para la gestión de pedidos, caja e inventario.">
    <meta name="robots" content="noindex">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: var(--bg-base);
            color: var(--text-strong);
            display: flex;
            flex-direction: column;
            position: relative;
            overflow-x: hidden;
        }
        /* Halos suaves de marca, como en el login */
        body::before {
            content: '';
            position: absolute;
            top: -30%; right: -20%;
            width: 60%; height: 80%;
            background: radial-gradient(circle, rgba(220, 38, 38, 0.07) 0%, transparent 70%);
            pointer-events: none;
        }
        body::after {
            content: '';
            position: absolute;
            bottom: -30%; left: -15%;
            width: 50%; height: 70%;
            background: radial-gradient(circle, rgba(249, 115, 22, 0.05) 0%, transparent 70%);
            pointer-events: none;
        }

        .top-bar {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1.25rem 1.75rem;
        }
        .brand-icon {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
        }
        .brand-text { font-size: 1.15rem; font-weight: 800; letter-spacing: -0.01em; }
        .brand-text span { color: #f97316; }

        .main {
            position: relative;
            z-index: 1;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem 3rem;
        }
        .content { width: 100%; max-width: 780px; text-align: center; }

        .eyebrow {
            display: inline-block;
            font-size: 0.75rem;
            font-weight: 700;
            color: #f97316;
            background: rgba(249, 115, 22, 0.1);
            border: 1px solid rgba(249, 115, 22, 0.25);
            padding: 0.3rem 0.85rem;
            border-radius: 50px;
            margin-bottom: 1.25rem;
        }
        h1 {
            font-size: clamp(1.9rem, 5vw, 2.75rem);
            font-weight: 900;
            line-height: 1.1;
            letter-spacing: -0.02em;
            margin-bottom: 1rem;
        }
        .lead {
            font-size: 1.02rem;
            line-height: 1.6;
            color: var(--text-secondary);
            max-width: 560px;
            margin: 0 auto 2rem;
        }
        .btn-login {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            background: linear-gradient(135deg, #f97316, #dc2626);
            color: #fff;
            text-decoration: none;
            font-weight: 800;
            font-size: 1rem;
            padding: 0.9rem 2rem;
            border-radius: 14px;
            box-shadow: 0 8px 20px rgba(220, 38, 38, 0.25);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 12px 26px rgba(220, 38, 38, 0.3); }
        .btn-login:active { transform: scale(0.98); }

        .access-note {
            margin-top: 1rem;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .roles {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 0.9rem;
            margin-top: 3rem;
            text-align: left;
        }
        .role-card {
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 1.15rem 1.25rem;
        }
        .role-card h3 {
            font-size: 0.92rem;
            font-weight: 800;
            margin-bottom: 0.35rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .role-card p { font-size: 0.82rem; line-height: 1.5; color: var(--text-muted); }
        .role-icon { color: #f97316; flex-shrink: 0; }

        .footer {
            position: relative;
            z-index: 1;
            border-top: 1px solid var(--border);
            padding: 1.25rem 1.75rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem 1.25rem;
            justify-content: space-between;
            font-size: 0.78rem;
            color: var(--text-muted);
        }
        @media (max-width: 640px) {
            .footer { justify-content: center; text-align: center; }
        }
    </style>
</head>
<body>

    <header class="top-bar">
        <div class="brand-icon">🍗</div>
        <div class="brand-text">Alitas <span>La Vieja</span></div>
    </header>

    <main class="main">
        <div class="content">
            <span class="eyebrow">Sistema interno</span>

            <h1>Punto de Venta<br>Alitas La Vieja</h1>

            <p class="lead">
                Plataforma de gestión de pedidos, caja e inventario para las sucursales
                de Cochabamba y Tarija. Para usarla, inicia sesión con tu cuenta.
            </p>

            <a href="{{ route('login') }}" class="btn-login">
                Iniciar sesión
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            </a>

            <p class="access-note">
                Acceso exclusivo para personal autorizado. Cada cuenta ve solo las herramientas de su rol.
            </p>

            <div class="roles">
                <div class="role-card">
                    <h3>
                        <svg class="role-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Cajero
                    </h3>
                    <p>Toma pedidos en el POS, cobra en caja y consulta el inventario de su sucursal.</p>
                </div>
                <div class="role-card">
                    <h3>
                        <svg class="role-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        Administrador de sucursal
                    </h3>
                    <p>Además del POS y la caja, revisa los reportes y el cierre diario de su sucursal.</p>
                </div>
                <div class="role-card">
                    <h3>
                        <svg class="role-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        Dueño
                    </h3>
                    <p>Panel completo: ventas de ambas sucursales, menú, promociones, personal y reportes.</p>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <span>© {{ date('Y') }} Alitas La Vieja · Cochabamba y Tarija</span>
        <span>Uso interno · Sistema de Punto de Venta</span>
    </footer>

</body>
</html>
