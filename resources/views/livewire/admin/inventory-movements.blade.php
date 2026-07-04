<div>
    <style>
        .mov-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
        .mov-title { font-size: 1.75rem; font-weight: 900; display: inline-flex; align-items: center; gap: 0.6rem; }
        .mov-title-icon { color: #f97316; flex-shrink: 0; }
        .mov-title-text { background: linear-gradient(135deg, #f97316, #dc2626); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .mov-btn-pdf {
            display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.7rem 1.25rem;
            background: linear-gradient(135deg, #dc2626, #b91c1c); color: #fff; font-weight: 800;
            font-size: 0.85rem; border: none; border-radius: 12px; cursor: pointer; text-decoration: none;
        }
        .mov-btn-pdf:hover { background: linear-gradient(135deg, #ef4444, #dc2626); }

        .mov-filters { display: flex; gap: 0.75rem; margin-bottom: 1.5rem; flex-wrap: wrap; align-items: flex-end; }
        .mov-field { display: flex; flex-direction: column; gap: 0.3rem; }
        .mov-field label { font-size: 0.7rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; }
        .mov-input, .mov-select {
            background: var(--bg-surface); border: 1px solid var(--border); color: var(--text); padding: 0.55rem 0.8rem;
            border-radius: 10px; font-size: 0.85rem; font-family: inherit; outline: none;
        }
        .mov-input:focus, .mov-select:focus { border-color: #f97316; }

        .mov-table-wrap { background: var(--bg-surface); border: 1px solid var(--border); border-radius: 16px; overflow: hidden; }
        .mov-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
        .mov-table thead { background: var(--bg-base); }
        .mov-table th { padding: 0.75rem 1rem; text-align: left; font-size: 0.7rem; font-weight: 700; color: var(--text-faint); text-transform: uppercase; letter-spacing: 0.06em; border-bottom: 1px solid var(--border); }
        .mov-table td { padding: 0.7rem 1rem; color: var(--text-secondary); border-bottom: 1px solid var(--bg-elevated); }
        .mov-table tbody tr:last-child td { border-bottom: none; }
        .mov-tag { font-size: 0.7rem; font-weight: 700; padding: 0.15rem 0.5rem; border-radius: 50px; }
        .mov-empty { text-align: center; padding: 3rem 1rem; color: var(--text-muted); }
        .mov-pag { margin-top: 1rem; }
    </style>

    @php
        $tipoMov = ['in' => 'Entrada', 'out' => 'Salida', 'adjustment' => 'Ajuste', 'sale' => 'Venta'];
        $tipoColor = ['in' => '#22c55e', 'out' => '#f97316', 'adjustment' => '#60a5fa', 'sale' => '#a78bfa'];
    @endphp

    <div class="mov-header">
        <h1 class="mov-title">
            <svg class="mov-title-icon" width="26" height="26" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10a2 2 0 002 2h12a2 2 0 002-2V7M4 7l8-4 8 4M4 7l8 4 8-4M12 11v8"></path></svg>
            <span class="mov-title-text">Historial de Movimientos</span>
        </h1>
        <a href="{{ route('admin.inventory.movements.export', ['date_from' => $dateFrom, 'date_to' => $dateTo, 'branch_id' => $branchId, 'type' => $type]) }}"
           target="_blank" class="mov-btn-pdf">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M4 6a2 2 0 012-2h8l6 6v8a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"></path></svg>
            Reporte PDF
        </a>
    </div>

    <div class="mov-filters">
        <div class="mov-field">
            <label>Desde</label>
            <input type="date" wire:model.live="dateFrom" class="mov-input">
        </div>
        <div class="mov-field">
            <label>Hasta</label>
            <input type="date" wire:model.live="dateTo" class="mov-input">
        </div>
        <div class="mov-field">
            <label>Sucursal</label>
            <select wire:model.live="branchId" class="mov-select">
                <option value="">Todas</option>
                @foreach($branches as $b)
                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mov-field">
            <label>Tipo</label>
            <select wire:model.live="type" class="mov-select">
                <option value="">Todos</option>
                <option value="in">Entrada</option>
                <option value="out">Salida</option>
                <option value="adjustment">Ajuste</option>
                <option value="sale">Venta</option>
            </select>
        </div>
        <div class="mov-field" style="flex: 1; min-width: 180px;">
            <label>Buscar producto</label>
            <input type="text" wire:model.live.debounce.300ms="search" class="mov-input" placeholder="Nombre del producto...">
        </div>
    </div>

    <div class="mov-table-wrap">
        <table class="mov-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Producto</th>
                    <th>Tipo</th>
                    <th style="text-align:right;">Cantidad</th>
                    <th style="text-align:right;">Stock</th>
                    <th>Sucursal</th>
                    <th>Usuario</th>
                    <th>Motivo</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movements as $m)
                    <tr>
                        <td style="color: var(--text-muted); white-space: nowrap;">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                        <td style="color: var(--text); font-weight: 600;">{{ $m->productVariant->product->name ?? '—' }} <span style="color: var(--text-muted); font-weight: 400;">/ {{ $m->productVariant->name ?? '' }}</span></td>
                        <td><span class="mov-tag" style="color: {{ $tipoColor[$m->type] ?? 'var(--text-muted)' }}; background: rgba(127,127,127,0.08);">{{ $tipoMov[$m->type] ?? $m->type }}</span></td>
                        <td style="text-align:right; font-weight:700;">{{ $m->quantity }}</td>
                        <td style="text-align:right; color: var(--text-muted);">{{ $m->stock_before }} → {{ $m->stock_after }}</td>
                        <td>{{ $m->branch->name ?? '—' }}</td>
                        <td>{{ $m->user->name ?? 'Sistema' }}</td>
                        <td style="color: var(--text-muted);">{{ $m->reason ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="mov-empty">No hay movimientos para los filtros seleccionados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mov-pag">
        {{ $movements->links() }}
    </div>
</div>
