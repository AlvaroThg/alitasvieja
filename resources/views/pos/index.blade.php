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
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(220, 38, 38, 0.3);
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
        /* Tab Bar Segmented Control */
        .pos-tab-container {
            display: inline-flex;
            gap: 0.4rem;
            margin-bottom: 1.25rem;
            background: var(--bg-surface);
            border: 1px solid var(--border-strong);
            padding: 0.35rem;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
            max-width: 440px;
            width: 100%;
        }
        .pos-tab-btn {
            flex: 1;
            padding: 0.65rem 1.1rem;
            border-radius: 12px;
            font-weight: 800;
            font-size: 0.88rem;
            letter-spacing: 0.01em;
            border: none;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            outline: none;
        }
        .pos-tab-btn-active {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(220, 38, 38, 0.35);
        }
        .pos-tab-btn-inactive {
            background: transparent;
            color: var(--text-muted);
        }
        .pos-tab-btn-inactive:hover {
            background: var(--bg-base);
            color: var(--text-strong);
        }
    </style>
</head>
<body>

    <nav class="pos-navbar">
        <div class="nav-brand">
            <div class="nav-brand-icon">
                <svg width="20" height="20" fill="white" viewBox="0 0 24 24">
                    <path d="M12 2C9 6 7 9 7 13a5 5 0 0010 0c0-1.5-.5-3-1.5-4.5C15 11 13.5 12 12 12c1-2 1-5 0-10z"/>
                </svg>
            </div>
            <div class="nav-brand-text">Alitas <span>La Vieja</span> — POS</div>
        </div>
        <div class="nav-info">
            @if(auth()->user()->isOwner())
            <a href="{{ route('admin.dashboard') }}" class="btn-back" style="margin-bottom: 0; padding: 0.4rem 0.8rem; font-size: 0.75rem;">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Dashboard
            </a>
            @endif
            @if(auth()->user()->isCashier() || auth()->user()->isBranchAdmin())
            <a href="{{ route('cash.movements') }}" class="btn-back" style="margin-bottom: 0; padding: 0.4rem 0.8rem; font-size: 0.75rem;">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"></path></svg>
                Caja
            </a>
            <a href="{{ route('admin.inventory.index') }}" class="btn-back" style="margin-bottom: 0; padding: 0.4rem 0.8rem; font-size: 0.75rem;">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                Inventario
            </a>
            @endif
            {{-- El admin de sucursal además accede a los reportes de su sucursal --}}
            @if(auth()->user()->isBranchAdmin())
            <a href="{{ route('admin.reports.cash.movements') }}" class="btn-back" style="margin-bottom: 0; padding: 0.4rem 0.8rem; font-size: 0.75rem;">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Mov. Caja
            </a>
            <a href="{{ route('admin.inventory.movements') }}" class="btn-back" style="margin-bottom: 0; padding: 0.4rem 0.8rem; font-size: 0.75rem;">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10a2 2 0 002 2h12a2 2 0 002-2V7M4 7l8-4 8 4M4 7l8 4 8-4M12 11v8"></path></svg>
                Mov. Inventario
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
        view: 'order', 
        printTickets(payload) { 
            let urls = payload.urls || payload;
            let toOpen = Array.isArray(urls) ? urls : [urls];
            
            if (toOpen.length === 1) {
                // Un solo ticket: abrir directamente
                window.open(toOpen[0], 'PrintTicket', 'width=420,height=650');
            } else if (toOpen.length >= 2) {
                // Dos tickets: abrir el primero en una ventana,
                // y el segundo en otra ventana inmediatamente (ambos en el mismo click context).
                let w1 = window.open(toOpen[0], 'PrintTicketA', 'width=420,height=650');
                let w2 = window.open(toOpen[1], 'PrintTicketB', 'width=420,height=650');
                
                // Si Chrome bloqueó el segundo, reintentamos con la primera ventana
                if (!w2 || w2.closed) {
                    setTimeout(() => {
                        if (w1 && !w1.closed) {
                            w1.location.href = toOpen[1];
                        } else {
                            window.open(toOpen[1], 'PrintTicketB', 'width=420,height=650');
                        }
                    }, 3000);
                }
            }
        } 
    }" @table-selected.window="view = 'order'" @edit-order.window="view = 'order'" @order-saved.window="view = 'order'; printTickets($event.detail[0] || $event.detail)">
        
        <!-- Pestañas Principales POS -->
        <div class="pos-tab-container">
            <button @click="view = 'order'"
                    :class="{ 'pos-tab-btn-active': view === 'order', 'pos-tab-btn-inactive': view !== 'order' }"
                    class="pos-tab-btn">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 022 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                </svg>
                <span>Toma de Pedido</span>
            </button>
            <button @click="view = 'tables'"
                    :class="{ 'pos-tab-btn-active': view === 'tables', 'pos-tab-btn-inactive': view !== 'tables' }"
                    class="pos-tab-btn">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                </svg>
                <span>Plano de Mesas</span>
            </button>
        </div>

        <div x-show="view === 'tables'" x-transition style="display: none;">
            <livewire:pos.table-grid />
        </div>
        
        <div x-show="view === 'order'" x-transition>
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