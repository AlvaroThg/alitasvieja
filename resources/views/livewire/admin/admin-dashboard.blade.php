<div>
    {{-- ═══ FILTROS ═══ --}}
    <div class="filters-bar">
        <div class="filters-group">
            <span class="filters-label">Período:</span>
            <div class="pill-group">
                <button wire:click="setPeriod('today')"
                        class="pill {{ $period === 'today' ? 'pill--active' : '' }}">
                    Hoy
                </button>
                <button wire:click="setPeriod('week')"
                        class="pill {{ $period === 'week' ? 'pill--active' : '' }}">
                    Semana
                </button>
                <button wire:click="setPeriod('month')"
                        class="pill {{ $period === 'month' ? 'pill--active' : '' }}">
                    Mes
                </button>
            </div>
        </div>

        <div class="filters-group">
            <span class="filters-label">Sucursal:</span>
            <select wire:model.live="branchId" class="branch-select" id="branch-filter-select">
                <option value="">Todas las sucursales</option>
                @foreach ($branches as $b)
                    <option value="{{ $b['id'] }}">{{ $b['name'] }}</option>
                @endforeach
            </select>
        </div>

        {{-- Loading indicator --}}
        <div wire:loading class="loading-indicator">
            <div class="spinner"></div>
            <span>Actualizando…</span>
        </div>
    </div>

    {{-- ═══ KPI CARDS ═══ --}}
    <div class="kpi-grid">
        {{-- Ingresos Totales --}}
        <div class="kpi-card">
            <div class="kpi-icon kpi-icon--revenue">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-value">${{ number_format($summary['total_revenue'] ?? 0, 2) }}</span>
                <span class="kpi-label">Ingresos Totales</span>
            </div>
            @if (($summary['revenue_vs_previous'] ?? 0) != 0)
                <div class="kpi-badge {{ ($summary['revenue_vs_previous'] ?? 0) > 0 ? 'kpi-badge--up' : 'kpi-badge--down' }}">
                    @if (($summary['revenue_vs_previous'] ?? 0) > 0)
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                    @else
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                    @endif
                    {{ abs($summary['revenue_vs_previous'] ?? 0) }}%
                </div>
            @endif
        </div>

        {{-- Pedidos Pagados --}}
        <div class="kpi-card">
            <div class="kpi-icon kpi-icon--orders">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-value">{{ number_format($summary['total_orders'] ?? 0) }}</span>
                <span class="kpi-label">Pedidos Pagados</span>
            </div>
        </div>

        {{-- Ticket Promedio --}}
        <div class="kpi-card">
            <div class="kpi-icon kpi-icon--ticket">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-value">${{ number_format($summary['average_ticket'] ?? 0, 2) }}</span>
                <span class="kpi-label">Ticket Promedio</span>
            </div>
        </div>

        {{-- Cancelados --}}
        <div class="kpi-card">
            <div class="kpi-icon kpi-icon--cancelled">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-value">{{ number_format($summary['cancelled_orders'] ?? 0) }}</span>
                <span class="kpi-label">Cancelados</span>
            </div>
        </div>
    </div>

    {{-- ═══ GRÁFICOS ROW 1: Ventas por Sucursal + Métodos de Pago ═══ --}}
    <div class="charts-row">
        <div class="chart-card chart-card--wide">
            <div class="chart-header">
                <h3 class="chart-title">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Ventas por Sucursal
                </h3>
            </div>
            <div class="chart-body">
                <canvas id="salesByBranchChart" wire:ignore></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                    Métodos de Pago
                </h3>
            </div>
            <div class="chart-body chart-body--doughnut">
                <canvas id="paymentMethodsChart" wire:ignore></canvas>
            </div>
        </div>
    </div>

    {{-- ═══ GRÁFICO: Tendencia de Ventas ═══ --}}
    <div class="chart-card chart-card--full">
        <div class="chart-header">
            <h3 class="chart-title">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                Tendencia de Ventas
            </h3>
        </div>
        <div class="chart-body chart-body--trend">
            <canvas id="salesTrendChart" wire:ignore></canvas>
        </div>
    </div>

    {{-- ═══ TABLAS ROW: Top Productos + Ventas por Sucursal ═══ --}}
    <div class="tables-row">
        {{-- Top Productos --}}
        <div class="table-card">
            <div class="table-header">
                <h3 class="table-title">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                    Top Productos
                </h3>
            </div>
            <div class="table-body">
                @if (count($topProducts) > 0)
                    @php $maxQty = max(array_column($topProducts, 'total_vendido')); @endphp
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Producto</th>
                                <th class="text-right">Cantidad</th>
                                <th class="text-right">Ingresos</th>
                                <th>Participación</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($topProducts as $i => $product)
                                <tr>
                                    <td class="rank-cell">
                                        @if ($i === 0)
                                            <span class="rank-badge rank-badge--gold">1</span>
                                        @elseif ($i === 1)
                                            <span class="rank-badge rank-badge--silver">2</span>
                                        @elseif ($i === 2)
                                            <span class="rank-badge rank-badge--bronze">3</span>
                                        @else
                                            <span class="rank-number">{{ $i + 1 }}</span>
                                        @endif
                                    </td>
                                    <td class="product-name">{{ $product['name'] }}</td>
                                    <td class="text-right font-mono">{{ number_format($product['total_vendido']) }}</td>
                                    <td class="text-right font-mono">${{ number_format($product['revenue'], 2) }}</td>
                                    <td>
                                        <div class="progress-bar-container">
                                            <div class="progress-bar" style="width: {{ $maxQty > 0 ? round(($product['total_vendido'] / $maxQty) * 100) : 0 }}%"></div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">
                        <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        <p>Sin datos para este período</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Ventas por Sucursal --}}
        <div class="table-card">
            <div class="table-header">
                <h3 class="table-title">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    Ventas por Sucursal
                </h3>
            </div>
            <div class="table-body">
                @if (count($revenueByBranch) > 0)
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Sucursal</th>
                                <th class="text-right">Pedidos</th>
                                <th class="text-right">Ingresos</th>
                                <th class="text-right">Ticket Prom.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($revenueByBranch as $branch)
                                <tr>
                                    <td>
                                        <span class="branch-dot"></span>
                                        {{ $branch['branch_name'] }}
                                    </td>
                                    <td class="text-right font-mono">{{ number_format($branch['orders']) }}</td>
                                    <td class="text-right font-mono">${{ number_format($branch['revenue'], 2) }}</td>
                                    <td class="text-right font-mono">
                                        ${{ $branch['orders'] > 0 ? number_format($branch['revenue'] / $branch['orders'], 2) : '0.00' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td><strong>Total</strong></td>
                                <td class="text-right font-mono"><strong>{{ number_format(array_sum(array_column($revenueByBranch, 'orders'))) }}</strong></td>
                                <td class="text-right font-mono"><strong>${{ number_format(array_sum(array_column($revenueByBranch, 'revenue')), 2) }}</strong></td>
                                <td class="text-right font-mono">
                                    @php
                                        $totalOrders = array_sum(array_column($revenueByBranch, 'orders'));
                                        $totalRev = array_sum(array_column($revenueByBranch, 'revenue'));
                                    @endphp
                                    <strong>${{ $totalOrders > 0 ? number_format($totalRev / $totalOrders, 2) : '0.00' }}</strong>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                @else
                    <div class="empty-state">
                        <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        <p>Sin datos para este período</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ═══ ESTILOS DEL DASHBOARD ═══ --}}
    <style>
        /* ── Filtros ── */
        .filters-bar {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
            padding: 1.25rem 1.5rem;
            background: linear-gradient(145deg, #141414, #1a1a1a);
            border: 1px solid #2a2a2a;
            border-radius: 16px;
        }
        .filters-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .filters-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .pill-group {
            display: flex;
            gap: 0.35rem;
            background: #0a0a0a;
            padding: 4px;
            border-radius: 12px;
            border: 1px solid #222;
        }
        .pill {
            padding: 0.5rem 1.1rem;
            border: none;
            border-radius: 10px;
            background: transparent;
            color: #888;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.25s ease;
            font-family: 'Inter', sans-serif;
        }
        .pill:hover {
            color: #ccc;
            background: rgba(255,255,255,0.04);
        }
        .pill--active {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: #fff;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
        }
        .pill--active:hover {
            color: #fff;
        }
        .branch-select {
            background: #0a0a0a;
            color: #fff;
            border: 1px solid #333;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            font-size: 0.85rem;
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            cursor: pointer;
            min-width: 180px;
            transition: border-color 0.2s ease;
        }
        .branch-select:focus {
            outline: none;
            border-color: #f97316;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
        }
        .loading-indicator {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #f97316;
            font-size: 0.8rem;
            font-weight: 600;
            margin-left: auto;
        }
        .spinner {
            width: 18px;
            height: 18px;
            border: 2px solid rgba(249, 115, 22, 0.2);
            border-top-color: #f97316;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* ── KPI Cards ── */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }
        @media (max-width: 900px) {
            .kpi-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 500px) {
            .kpi-grid { grid-template-columns: 1fr; }
        }
        .kpi-card {
            background: linear-gradient(145deg, #141414, #1a1a1a);
            border: 1px solid #2a2a2a;
            border-radius: 16px;
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .kpi-card:hover {
            border-color: #333;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.3);
        }
        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, rgba(249, 115, 22, 0.3), transparent);
        }
        .kpi-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .kpi-icon--revenue {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.15), rgba(34, 197, 94, 0.05));
            color: #22c55e;
        }
        .kpi-icon--orders {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(59, 130, 246, 0.05));
            color: #3b82f6;
        }
        .kpi-icon--ticket {
            background: linear-gradient(135deg, rgba(249, 115, 22, 0.15), rgba(249, 115, 22, 0.05));
            color: #f97316;
        }
        .kpi-icon--cancelled {
            background: linear-gradient(135deg, rgba(220, 38, 38, 0.15), rgba(220, 38, 38, 0.05));
            color: #dc2626;
        }
        .kpi-content {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
        }
        .kpi-value {
            font-size: 1.6rem;
            font-weight: 900;
            color: #fff;
            letter-spacing: -0.02em;
            line-height: 1.1;
        }
        .kpi-label {
            font-size: 0.75rem;
            color: #666;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .kpi-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            display: flex;
            align-items: center;
            gap: 0.2rem;
            padding: 0.2rem 0.6rem;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 700;
        }
        .kpi-badge--up {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.25);
            color: #22c55e;
        }
        .kpi-badge--down {
            background: rgba(220, 38, 38, 0.1);
            border: 1px solid rgba(220, 38, 38, 0.25);
            color: #ef4444;
        }

        /* ── Charts ── */
        .charts-row {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        @media (max-width: 768px) {
            .charts-row { grid-template-columns: 1fr; }
        }
        .chart-card {
            background: linear-gradient(145deg, #141414, #1a1a1a);
            border: 1px solid #2a2a2a;
            border-radius: 16px;
            overflow: hidden;
        }
        .chart-card--full {
            margin-bottom: 2rem;
        }
        .chart-header {
            padding: 1.25rem 1.5rem 0;
        }
        .chart-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
            font-weight: 700;
            color: #fff;
        }
        .chart-title svg {
            color: #f97316;
        }
        .chart-body {
            padding: 1rem 1.5rem 1.5rem;
            position: relative;
            height: 280px;
        }
        .chart-body--doughnut {
            height: 280px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .chart-body--trend {
            height: 300px;
        }

        /* ── Tables ── */
        .tables-row {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        @media (max-width: 768px) {
            .tables-row { grid-template-columns: 1fr; }
        }
        .table-card {
            background: linear-gradient(145deg, #141414, #1a1a1a);
            border: 1px solid #2a2a2a;
            border-radius: 16px;
            overflow: hidden;
        }
        .table-header {
            padding: 1.25rem 1.5rem 0;
        }
        .table-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
            font-weight: 700;
            color: #fff;
        }
        .table-title svg {
            color: #f97316;
        }
        .table-body {
            padding: 1rem 1.5rem 1.5rem;
            overflow-x: auto;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table th {
            font-size: 0.7rem;
            font-weight: 600;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            padding: 0.6rem 0.75rem;
            text-align: left;
            border-bottom: 1px solid #222;
        }
        .data-table td {
            font-size: 0.85rem;
            color: #ccc;
            padding: 0.7rem 0.75rem;
            border-bottom: 1px solid #1a1a1a;
            transition: background 0.2s ease;
        }
        .data-table tbody tr:hover td {
            background: rgba(255,255,255,0.02);
        }
        .data-table tfoot td {
            border-top: 1px solid #333;
            border-bottom: none;
            color: #fff;
            padding-top: 0.9rem;
        }
        .text-right { text-align: right; }
        .font-mono { font-variant-numeric: tabular-nums; }
        .product-name {
            font-weight: 600;
            color: #fff;
        }
        .rank-cell { width: 40px; }
        .rank-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 800;
        }
        .rank-badge--gold {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #fff;
            box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
        }
        .rank-badge--silver {
            background: linear-gradient(135deg, #94a3b8, #64748b);
            color: #fff;
        }
        .rank-badge--bronze {
            background: linear-gradient(135deg, #b45309, #92400e);
            color: #fff;
        }
        .rank-number {
            color: #555;
            font-weight: 600;
            font-size: 0.8rem;
        }
        .progress-bar-container {
            width: 100%;
            height: 8px;
            background: #1a1a1a;
            border-radius: 4px;
            overflow: hidden;
            min-width: 80px;
        }
        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #dc2626, #f97316);
            border-radius: 4px;
            transition: width 0.6s ease;
        }
        .branch-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: linear-gradient(135deg, #dc2626, #f97316);
            margin-right: 0.5rem;
        }
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #444;
        }
        .empty-state svg {
            margin-bottom: 0.75rem;
            opacity: 0.5;
        }
        .empty-state p {
            font-size: 0.85rem;
            font-weight: 500;
        }
    </style>

    {{-- ═══ CHART.JS INTEGRATION ═══ --}}
    @script
    <script>
        // Chart instances
        let salesByBranchChart = null;
        let paymentMethodsChart = null;
        let salesTrendChart = null;

        // Color palette
        const COLORS = {
            red:    '#dc2626',
            orange: '#f97316',
            amber:  '#f59e0b',
            green:  '#22c55e',
            blue:   '#3b82f6',
            purple: '#a855f7',
            pink:   '#ec4899',
            cyan:   '#06b6d4',
        };
        const CHART_COLORS = [
            COLORS.red, COLORS.orange, COLORS.amber,
            COLORS.green, COLORS.blue, COLORS.purple,
            COLORS.pink, COLORS.cyan,
        ];

        // Global Chart.js defaults
        Chart.defaults.color = '#666';
        Chart.defaults.borderColor = 'rgba(255,255,255,0.06)';
        Chart.defaults.font.family = "'Inter', sans-serif";

        // Initialize charts on mount
        initCharts();

        // Load initial data since mount() dispatch doesn't trigger JS
        const initialData = {
            revenueByBranch: $wire.get('revenueByBranch'),
            paymentBreakdown: $wire.get('paymentBreakdown'),
            salesSeries: $wire.get('salesSeries')
        };
        setTimeout(() => updateCharts(initialData), 50);

        // Listen for Livewire updates
        $wire.on('chartsUpdated', (eventData) => {
            const data = eventData[0] || eventData;
            updateCharts(data);
        });

        function initCharts() {
            // ── Sales by Branch (Horizontal Bar) ──
            const branchCtx = document.getElementById('salesByBranchChart');
            if (branchCtx) {
                salesByBranchChart = new Chart(branchCtx, {
                    type: 'bar',
                    data: {
                        labels: [],
                        datasets: [{
                            label: 'Ingresos (Bs)',
                            data: [],
                            backgroundColor: CHART_COLORS.map(c => c + '33'),
                            borderColor: CHART_COLORS,
                            borderWidth: 2,
                            borderRadius: 8,
                            borderSkipped: false,
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#1a1a1a',
                                titleColor: '#fff',
                                bodyColor: '#ccc',
                                borderColor: '#333',
                                borderWidth: 1,
                                padding: 12,
                                cornerRadius: 10,
                                callbacks: {
                                    label: ctx => `$${ctx.parsed.x.toLocaleString('es-BO', {minimumFractionDigits:2})}`
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { color: 'rgba(255,255,255,0.04)' },
                                ticks: {
                                    callback: v => '$' + (v >= 1000 ? (v/1000).toFixed(1) + 'K' : v),
                                    font: { size: 11 }
                                }
                            },
                            y: {
                                grid: { display: false },
                                ticks: { font: { size: 12, weight: '600' }, color: '#ccc' }
                            }
                        }
                    }
                });
            }

            // ── Payment Methods (Doughnut) ──
            const payCtx = document.getElementById('paymentMethodsChart');
            if (payCtx) {
                paymentMethodsChart = new Chart(payCtx, {
                    type: 'doughnut',
                    data: {
                        labels: [],
                        datasets: [{
                            data: [],
                            backgroundColor: [COLORS.green, COLORS.blue, COLORS.orange, COLORS.purple, COLORS.pink],
                            borderColor: '#141414',
                            borderWidth: 3,
                            hoverBorderColor: '#333',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 16,
                                    usePointStyle: true,
                                    pointStyleWidth: 10,
                                    font: { size: 12, weight: '600' },
                                    color: '#ccc'
                                }
                            },
                            tooltip: {
                                backgroundColor: '#1a1a1a',
                                titleColor: '#fff',
                                bodyColor: '#ccc',
                                borderColor: '#333',
                                borderWidth: 1,
                                padding: 12,
                                cornerRadius: 10,
                                callbacks: {
                                    label: ctx => {
                                        const total = ctx.dataset.data.reduce((a,b) => a+b, 0);
                                        const pct = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
                                        return `$${ctx.parsed.toLocaleString('es-BO', {minimumFractionDigits:2})} (${pct}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // ── Sales Trend (Line) ──
            const trendCtx = document.getElementById('salesTrendChart');
            if (trendCtx) {
                salesTrendChart = new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: [],
                        datasets: [
                            {
                                label: 'Ingresos (Bs)',
                                data: [],
                                borderColor: COLORS.orange,
                                backgroundColor: function(context) {
                                    const chart = context.chart;
                                    const {ctx, chartArea} = chart;
                                    if (!chartArea) return 'transparent';
                                    const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                                    gradient.addColorStop(0, 'rgba(249, 115, 22, 0.2)');
                                    gradient.addColorStop(1, 'rgba(249, 115, 22, 0.0)');
                                    return gradient;
                                },
                                borderWidth: 2.5,
                                fill: true,
                                tension: 0.4,
                                pointRadius: 4,
                                pointHoverRadius: 7,
                                pointBackgroundColor: COLORS.orange,
                                pointBorderColor: '#141414',
                                pointBorderWidth: 2,
                                yAxisID: 'y',
                            },
                            {
                                label: 'Pedidos',
                                data: [],
                                borderColor: COLORS.blue,
                                backgroundColor: 'transparent',
                                borderWidth: 2,
                                borderDash: [6, 3],
                                fill: false,
                                tension: 0.4,
                                pointRadius: 3,
                                pointHoverRadius: 6,
                                pointBackgroundColor: COLORS.blue,
                                pointBorderColor: '#141414',
                                pointBorderWidth: 2,
                                yAxisID: 'y1',
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: {
                                labels: {
                                    padding: 20,
                                    usePointStyle: true,
                                    pointStyleWidth: 10,
                                    font: { size: 12, weight: '600' },
                                    color: '#ccc'
                                }
                            },
                            tooltip: {
                                backgroundColor: '#1a1a1a',
                                titleColor: '#fff',
                                bodyColor: '#ccc',
                                borderColor: '#333',
                                borderWidth: 1,
                                padding: 12,
                                cornerRadius: 10,
                            }
                        },
                        scales: {
                            x: {
                                grid: { color: 'rgba(255,255,255,0.04)' },
                                ticks: { font: { size: 11 }, maxRotation: 45 }
                            },
                            y: {
                                type: 'linear',
                                position: 'left',
                                grid: { color: 'rgba(255,255,255,0.04)' },
                                ticks: {
                                    callback: v => '$' + (v >= 1000 ? (v/1000).toFixed(1) + 'K' : v),
                                    font: { size: 11 }
                                },
                                title: {
                                    display: true,
                                    text: 'Ingresos (Bs)',
                                    color: COLORS.orange,
                                    font: { size: 11, weight: '600' }
                                }
                            },
                            y1: {
                                type: 'linear',
                                position: 'right',
                                grid: { drawOnChartArea: false },
                                ticks: { font: { size: 11 }, stepSize: 1 },
                                title: {
                                    display: true,
                                    text: 'Pedidos',
                                    color: COLORS.blue,
                                    font: { size: 11, weight: '600' }
                                }
                            }
                        }
                    }
                });
            }

            // Initial data load
            const initialData = {
                revenueByBranch:  @json($revenueByBranch),
                paymentBreakdown: @json($paymentBreakdown),
                salesSeries:      @json($salesSeries),
            };
            updateCharts(initialData);
        }

        function updateCharts(data) {
            // ── Update Sales by Branch ──
            if (salesByBranchChart && data.revenueByBranch) {
                const branchData = data.revenueByBranch;
                salesByBranchChart.data.labels = branchData.map(b => b.branch_name);
                salesByBranchChart.data.datasets[0].data = branchData.map(b => b.revenue);
                salesByBranchChart.data.datasets[0].backgroundColor = branchData.map((_, i) => CHART_COLORS[i % CHART_COLORS.length] + '33');
                salesByBranchChart.data.datasets[0].borderColor = branchData.map((_, i) => CHART_COLORS[i % CHART_COLORS.length]);
                salesByBranchChart.update('active');
            }

            // ── Update Payment Methods ──
            if (paymentMethodsChart && data.paymentBreakdown) {
                const payData = data.paymentBreakdown;
                const methodLabels = {
                    'cash': 'Efectivo',
                    'card': 'Tarjeta',
                    'qr': 'QR',
                    'transfer': 'Transferencia',
                    'mixed': 'Mixto',
                };
                paymentMethodsChart.data.labels = payData.map(p => methodLabels[p.method] || p.method);
                paymentMethodsChart.data.datasets[0].data = payData.map(p => p.total_amount);
                paymentMethodsChart.update('active');
            }

            // ── Update Sales Trend ──
            if (salesTrendChart && data.salesSeries) {
                const series = data.salesSeries;
                // Format labels for better readability
                salesTrendChart.data.labels = (series.labels || []).map(l => {
                    if (l && l.length === 10) {
                        // YYYY-MM-DD → DD/MM
                        const parts = l.split('-');
                        return parts[2] + '/' + parts[1];
                    }
                    return l;
                });
                salesTrendChart.data.datasets[0].data = series.revenue || [];
                salesTrendChart.data.datasets[1].data = series.orders || [];
                salesTrendChart.update('active');
            }
        }
    </script>
    @endscript
</div>
