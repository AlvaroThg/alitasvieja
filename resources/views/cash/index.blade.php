<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caja y Movimientos - Alitas La Vieja</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-base);
            min-height: 100vh;
        }
        .pos-navbar {
            background: linear-gradient(135deg, var(--bg-surface) 0%, var(--bg-elevated) 100%);
            border-bottom: 1px solid var(--border);
            padding: 0 1.5rem;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 40;
        }
        .pos-navbar::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, #dc2626, #f97316, #dc2626);
            opacity: 0.6;
        }
        .nav-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .nav-brand-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2);
        }
        .nav-brand-text {
            color: var(--text-strong);
            font-size: 1.1rem;
            font-weight: 800;
            letter-spacing: -0.01em;
        }
        .nav-brand-text span {
            color: #f97316;
        }
        .main-content {
            padding: 3rem 1.5rem;
            display: flex;
            justify-content: center;
        }
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.25rem;
            background: var(--bg-surface);
            color: #f97316;
            font-weight: 700;
            font-size: 0.85rem;
            border: 1px solid var(--border);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            margin-bottom: 2rem;
            margin-left: 2rem;
            margin-top: 2rem;
        }
        .btn-back:hover {
            background: var(--bg-elevated);
            border-color: #f97316;
            transform: translateX(-2px);
        }
    </style>
</head>
<body>

    <nav class="pos-navbar">
        <div class="nav-brand">
            <div class="nav-brand-icon">🍗</div>
            <div class="nav-brand-text">Alitas <span>La Vieja</span> — Caja</div>
        </div>
        <div style="color: var(--text-strong); font-weight: 600;">
            {{ auth()->user()->name }}
        </div>
    </nav>

    @if(auth()->user()->isCashier())
    <a href="{{ route('pos.index') }}" class="btn-back">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Volver al POS
    </a>
    @else
    <a href="{{ route('admin.dashboard') }}" class="btn-back">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Volver al Dashboard
    </a>
    @endif

    <main class="main-content">
        <livewire:cash.cash-manager />
    </main>

    @livewireScripts
</body>
</html>
