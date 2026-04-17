<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Alitas Vega</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: #0a0a0a;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        /* Ambient glow background */
        body::before {
            content: '';
            position: absolute;
            top: -30%;
            left: -10%;
            width: 60%;
            height: 80%;
            background: radial-gradient(circle, rgba(220, 38, 38, 0.08) 0%, transparent 70%);
            pointer-events: none;
        }
        body::after {
            content: '';
            position: absolute;
            bottom: -20%;
            right: -10%;
            width: 50%;
            height: 70%;
            background: radial-gradient(circle, rgba(249, 115, 22, 0.06) 0%, transparent 70%);
            pointer-events: none;
        }
        .dashboard-card {
            background: linear-gradient(145deg, #141414, #1a1a1a);
            border: 1px solid #2a2a2a;
            border-radius: 24px;
            padding: 3rem;
            width: 100%;
            max-width: 520px;
            position: relative;
            z-index: 1;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5), 0 0 100px rgba(220, 38, 38, 0.03);
        }
        .dashboard-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, #dc2626, #f97316, #dc2626);
            border-radius: 24px 24px 0 0;
        }
        .brand-icon {
            width: 72px;
            height: 72px;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            box-shadow: 0 8px 24px rgba(220, 38, 38, 0.25);
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }
        .card-title {
            color: #fff;
            font-size: 1.75rem;
            font-weight: 800;
            text-align: center;
            margin-bottom: 0.25rem;
            letter-spacing: -0.02em;
        }
        .card-subtitle {
            color: #666;
            font-size: 0.9rem;
            text-align: center;
            margin-bottom: 2.5rem;
            font-weight: 500;
        }
        /* Stats row */
        .stats-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 0.75rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: #111;
            border: 1px solid #222;
            border-radius: 16px;
            padding: 1rem;
            text-align: center;
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            border-color: #333;
            transform: translateY(-2px);
        }
        .stat-value {
            font-size: 1.5rem;
            font-weight: 900;
            background: linear-gradient(135deg, #f97316, #dc2626);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .stat-label {
            font-size: 0.65rem;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 600;
            margin-top: 0.25rem;
        }
        /* Buttons */
        .btn-primary {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            width: 100%;
            padding: 1.1rem;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: #fff;
            font-size: 0.95rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            border: none;
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(220, 38, 38, 0.25);
            text-decoration: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(220, 38, 38, 0.35);
        }
        .btn-primary:active {
            transform: scale(0.97);
        }
        .btn-secondary {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 1rem;
            background: #111;
            color: #f97316;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 0.03em;
            border: 1px solid #222;
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        .btn-secondary:hover {
            background: #1a1a1a;
            border-color: #f97316;
            transform: translateY(-1px);
        }
        .btn-secondary:active {
            transform: scale(0.97);
        }
        .btn-logout {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.85rem;
            background: transparent;
            color: #555;
            font-size: 0.8rem;
            font-weight: 600;
            border: 1px solid #1e1e1e;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-logout:hover {
            color: #dc2626;
            border-color: #dc2626;
            background: rgba(220, 38, 38, 0.05);
        }
        .actions-stack {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, #2a2a2a, transparent);
            margin: 1rem 0;
        }
        .badge-online {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.2);
            color: #22c55e;
            padding: 0.3rem 0.75rem;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 600;
            margin: 0 auto;
            display: flex;
            justify-content: center;
            margin-bottom: 1.5rem;
        }
        .badge-online::before {
            content: '';
            width: 6px;
            height: 6px;
            background: #22c55e;
            border-radius: 50%;
            animation: pulse-dot 2s ease-in-out infinite;
        }
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }
    </style>
</head>
<body>

    <div class="dashboard-card">
        <div class="brand-icon">🍗</div>
        <h1 class="card-title">Alitas Vega</h1>
        <p class="card-subtitle">Panel de Administración</p>
        
        <div class="badge-online">Sistema en línea</div>

        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-value">12</div>
                <div class="stat-label">Mesas</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">--</div>
                <div class="stat-label">Órdenes</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">$0</div>
                <div class="stat-label">Ventas</div>
            </div>
        </div>

        <div class="actions-stack">
            <a href="{{ route('pos.index') }}" class="btn-primary">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                Entrar a Punto de Venta
            </a>

            <div class="divider"></div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Cerrar Sesión
                </button>
            </form>
        </div>
    </div>

</body>
</html>
