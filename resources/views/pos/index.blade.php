<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Punto de Venta - Alitas La Vieja</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
        .nav-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .nav-badge {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(249, 115, 22, 0.08);
            border: 1px solid rgba(249, 115, 22, 0.15);
            color: #f97316;
            padding: 0.35rem 0.85rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .nav-badge::before {
            content: '';
            width: 6px;
            height: 6px;
            background: #f97316;
            border-radius: 50%;
        }
        .nav-time {
            color: var(--text-faint);
            font-size: 0.8rem;
            font-weight: 500;
            font-variant-numeric: tabular-nums;
        }
        .pos-main {
            padding: 1.25rem 1.5rem;
        }
        /* Back button */
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
            margin-bottom: 1rem;
            text-decoration: none;
        }
        .btn-back:hover {
            background: var(--bg-elevated);
            border-color: #f97316;
            transform: translateX(-2px);
        }
        .btn-back:active {
            transform: scale(0.97);
        }
    </style>
</head>
<body>

    <nav class="pos-navbar">
        <div class="nav-brand">
            <div class="nav-brand-icon">🍗</div>
            <div class="nav-brand-text">Alitas <span>La Vieja</span> — POS</div>
        </div>
        <div class="nav-info">
            @if(auth()->user()->isOwner())
            <a href="{{ route('admin.dashboard') }}" class="btn-back" style="margin-bottom: 0; padding: 0.4rem 0.8rem; font-size: 0.75rem;">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Dashboard
            </a>
            @endif
            @if(auth()->user()->isCashier())
            <a href="{{ route('cash.movements') }}" class="btn-back" style="margin-bottom: 0; padding: 0.4rem 0.8rem; font-size: 0.75rem;">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"></path></svg>
                Caja
            </a>
            <a href="{{ route('admin.inventory.index') }}" class="btn-back" style="margin-bottom: 0; padding: 0.4rem 0.8rem; font-size: 0.75rem;">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                Inventario
            </a>
            @endif
            <div class="nav-badge">{{ auth()->user()->branch->name ?? 'Sin Sucursal' }}</div>
            <div class="nav-time" id="pos-clock"></div>
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit" style="background: transparent; border: 1px solid var(--border-strong); color: var(--text-muted); padding: 0.35rem 0.75rem; border-radius: 8px; font-size: 0.75rem; cursor: pointer;">Salir</button>
            </form>
        </div>
    </nav>

    <main class="pos-main" x-data="{ 
        view: 'tables', 
        printTickets(payload) { 
            let urls = payload.urls || payload;
            if(Array.isArray(urls)) {
                urls.forEach((url, i) => window.open(url, 'PrintTicket' + i, 'width=400,height=600'));
            } else if (typeof urls === 'string') {
                window.open(urls, 'PrintTicket', 'width=400,height=600');
            } else if (payload.url) {
                window.open(payload.url, 'PrintTicket', 'width=400,height=600');
            }
        } 
    }" @table-selected.window="view = 'order'" @order-saved.window="view = 'tables'; printTickets($event.detail[0] || $event.detail)">
        
        <div x-show="view === 'tables'" x-transition>
            <livewire:pos.table-grid />
        </div>
        
        <div x-show="view === 'order'" style="display: none;" x-transition>
            <!-- Botón para regresar a mesas -->
            <button @click="view = 'tables'" class="btn-back">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Volver a Mesas
            </button>
            <livewire:pos.order-builder />
        </div>
        
    </main>

    <script>
        // Reloj en tiempo real
        function updateClock() {
            const now = new Date();
            const h = now.getHours().toString().padStart(2, '0');
            const m = now.getMinutes().toString().padStart(2, '0');
            const s = now.getSeconds().toString().padStart(2, '0');
            const el = document.getElementById('pos-clock');
            if(el) el.textContent = h + ':' + m + ':' + s;
        }
        updateClock();
        setInterval(updateClock, 1000);
    </script>

</body>
</html>