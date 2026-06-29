<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Panel de Administración - Alitas La Vieja</title>
    <meta name="description" content="Panel de administración con KPIs, gráficos de ventas y reportes para Alitas La Vieja">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    {{-- Chart.js via CDN (sin pipeline npm) --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: var(--bg-base);
            color: var(--text-strong);
        }
        /* ── Navbar ── */
        .admin-navbar {
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
        .admin-navbar::after {
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
            font-size: 1.1rem;
            font-weight: 800;
            letter-spacing: -0.01em;
        }
        .nav-brand-text span {
            color: #f97316;
        }
        .nav-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .nav-user {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }
        .nav-links {
            display: flex;
            gap: 0.5rem;
        }
        .nav-link {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.45rem 0.9rem;
            background: var(--bg-surface);
            color: var(--text-muted);
            font-size: 0.78rem;
            font-weight: 600;
            border: 1px solid var(--border);
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .nav-link:hover {
            background: var(--bg-elevated);
            color: #f97316;
            border-color: #f97316;
        }
        .btn-logout-nav {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.45rem 0.9rem;
            background: transparent;
            color: var(--text-faint);
            font-size: 0.78rem;
            font-weight: 600;
            border: 1px solid var(--border);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: 'Inter', sans-serif;
        }
        .btn-logout-nav:hover {
            color: #dc2626;
            border-color: #dc2626;
            background: rgba(220, 38, 38, 0.05);
        }
        /* ── Main ── */
        .main-content {
            padding: 2rem 1.5rem;
            max-width: 1400px;
            margin: 0 auto;
        }
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
        }
        .page-title {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }
        .page-title-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            border-radius: 12px;
            margin-right: 0.75rem;
            font-size: 1.2rem;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2);
            vertical-align: middle;
        }
        .quick-links {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .quick-link {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.5rem 1rem;
            background: var(--bg-surface);
            color: #f97316;
            font-size: 0.8rem;
            font-weight: 700;
            border: 1px solid var(--border);
            border-radius: 12px;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .quick-link:hover {
            background: var(--bg-elevated);
            border-color: #f97316;
            transform: translateY(-1px);
        }
        @media (max-width: 768px) {
            .page-header { flex-direction: column; gap: 1rem; align-items: flex-start; }
            .nav-links { display: none; }
        }
    </style>
</head>
<body>

    <nav class="admin-navbar">
        <div class="nav-brand">
            <div class="nav-brand-icon">🍗</div>
            <div class="nav-brand-text">Alitas <span>La Vieja</span> — Dashboard</div>
        </div>
        <div class="nav-right">
            <div class="nav-links">
                <a href="{{ route('admin.products.index') }}" class="nav-link">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    Productos
                </a>
                <a href="{{ route('admin.inventory.index') }}" class="nav-link">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    Inventario
                </a>
                <a href="{{ route('admin.promotions.index') }}" class="nav-link">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                    Promos
                </a>
                <a href="{{ route('admin.categories.index') }}" class="nav-link">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    Categorías
                </a>
                <a href="{{ route('admin.sauces.index') }}" class="nav-link">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2v16z"></path></svg>
                    Salsas
                </a>
                <a href="{{ route('admin.users.index') }}" class="nav-link">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    Usuarios
                </a>
            </div>
            <span class="nav-user">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                @csrf
                <button type="submit" class="btn-logout-nav">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Salir
                </button>
            </form>
        </div>
    </nav>

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <span class="page-title-icon"><svg width="22" height="22" fill="none" stroke="#fff" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg></span>
                Dashboard
            </h1>
        </div>

        {{-- ════ Componente Livewire del Dashboard ════ --}}
        <livewire:admin.admin-dashboard />
    </main>

    @livewireScripts
</body>
</html>
