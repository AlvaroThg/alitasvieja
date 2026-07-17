<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salsas - Panel de Administración</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: var(--bg-base);
            color: var(--text-strong);
        }
        
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
        
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            background: var(--bg-surface);
            border: 1px solid var(--border);
            transition: all 0.2s ease;
        }
        .btn-back:hover {
            background: var(--bg-elevated);
            color: #f97316;
            border-color: #f97316;
        }

        .main-content {
            padding: 2rem 1.5rem;
            max-width: 1400px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <nav class="admin-navbar">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <a href="{{ route('admin.dashboard') }}" class="btn-back">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Volver al Dashboard
            </a>
            <h1 style="font-size: 1.1rem; font-weight: 800; color: var(--text-strong);">Gestión de Salsas</h1>
        </div>
    </nav>

    <main class="main-content">
        <livewire:admin.sauce-manager />
    </main>

    @livewireScripts
</body>
</html>
