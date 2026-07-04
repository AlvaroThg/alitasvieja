<div>
    <style>
        .cm-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
        .cm-title { font-size: 1.75rem; font-weight: 900; display: inline-flex; align-items: center; gap: 0.6rem; }
        .cm-title-icon { color: #f97316; flex-shrink: 0; }
        .cm-title-text { background: linear-gradient(135deg, #f97316, #dc2626); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .cm-btn-pdf {
            display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.7rem 1.25rem;
            background: linear-gradient(135deg, #dc2626, #b91c1c); color: #fff; font-weight: 800;
            font-size: 0.85rem; border: none; border-radius: 12px; cursor: pointer; text-decoration: none;
        }
        .cm-btn-pdf:hover { background: linear-gradient(135deg, #ef4444, #dc2626); }

        .cm-filters { display: flex; gap: 0.75rem; margin-bottom: 1.25rem; flex-wrap: wrap; align-items: flex-end; }
        .cm-field { display: flex; flex-direction: column; gap: 0.3rem; }
        .cm-field label { font-size: 0.7rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; }
        .cm-input, .cm-select {
            background: var(--bg-surface); border: 1px solid var(--border); color: var(--text); padding: 0.55rem 0.8rem;
            border-radius: 10px; font-size: 0.85rem; font-family: inherit; outline: none;
        }
        .cm-input:focus, .cm-select:focus { border-color: #f97316; }

        .cm-totals { display: flex; gap: 1rem; margin-bottom: 1.25rem; flex-wrap: wrap; }
        .cm-total-card { background: var(--bg-surface); border: 1px solid var(--border); border-radius: 14px; padding: 0.8rem 1.25rem; min-width: 170px; }
        .cm-total-label { font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); }
        .cm-total-value { font-size: 1.35rem; font-weight: 800; }

        .cm-table-wrap { background: var(--bg-surface); border: 1px solid var(--border); border-radius: 16px; overflow: hidden; }
        .cm-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
        .cm-table thead { background: var(--bg-base); }
        .cm-table th { padding: 0.75rem 1rem; text-align: left; font-size: 0.7rem; font-weight: 700; color: var(--text-faint); text-transform: uppercase; letter-spacing: 0.06em; border-bottom: 1px solid var(--border); }
        .cm-table td { padding: 0.7rem 1rem; color: var(--text-secondary); border-bottom: 1px solid var(--bg-elevated); }
        .cm-table tbody tr:last-child td { border-bottom: none; }
        .cm-tag { font-size: 0.62rem; font-weight: 700; padding: 0.12rem 0.45rem; border-radius: 6px; border: 1px solid; }
        .cm-empty { text-align: center; padding: 3rem 1rem; color: var(--text-muted); }
        .cm-pag { margin-top: 1rem; }
    </style>

    <div class="cm-header">
        <h1 class="cm-title">
            <svg class="cm-title-icon" width="26" height="26" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="cm-title-text">Movimientos de Caja</span>
        </h1>
        <a href="{{ route('admin.reports.cash.movements.export', ['date_from' => $dateFrom, 'date_to' => $dateTo, 'branch_id' => $branchId, 'type' => $type, 'cash_box' => $cashBox]) }}"
           target="_blank" class="cm-btn-pdf">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M4 6a2 2 0 012-2h8l6 6v8a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"></path></svg>
            Reporte PDF
        </a>
    </div>

    <div class="cm-filters">
        <div class="cm-field">
            <label>Desde</label>
            <input type="date" wire:model.live="dateFrom" class="cm-input">
        </div>
        <div class="cm-field">
            <label>Hasta</label>
            <input type="date" wire:model.live="dateTo" class="cm-input">
        </div>
        <div class="cm-field">
            <label>Sucursal</label>
            <select wire:model.live="branchId" class="cm-select">
                <option value="">Todas</option>
                @foreach($branches as $b)
                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="cm-field">
            <label>Tipo</label>
            <select wire:model.live="type" class="cm-select">
                <option value="">Todos</option>
                <option value="income">Ingreso</option>
                <option value="expense">Egreso</option>
            </select>
        </div>
        <div class="cm-field">
            <label>Caja</label>
            <select wire:model.live="cashBox" class="cm-select">
                <option value="">Todas</option>
                <option value="sales">Caja de Venta</option>
                <option value="petty">Caja Chica</option>
                <option value="transfer">Traspasos</option>
            </select>
        </div>
    </div>

    <div class="cm-totals">
        <div class="cm-total-card">
            <div class="cm-total-label">Total Ingresos</div>
            <div class="cm-total-value" style="color: #22c55e;">Bs. {{ number_format($totals['income'], 2) }}</div>
        </div>
        <div class="cm-total-card">
            <div class="cm-total-label">Total Egresos</div>
            <div class="cm-total-value" style="color: #ef4444;">Bs. {{ number_format($totals['expense'], 2) }}</div>
        </div>
        <div class="cm-total-card">
            <div class="cm-total-label">Balance</div>
            @php $balance = $totals['income'] - $totals['expense']; @endphp
            <div class="cm-total-value" style="color: {{ $balance >= 0 ? '#22c55e' : '#ef4444' }};">Bs. {{ number_format($balance, 2) }}</div>
        </div>
    </div>

    <div class="cm-table-wrap">
        <table class="cm-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Concepto</th>
                    <th>Caja</th>
                    <th>Tipo</th>
                    <th style="text-align:right;">Monto</th>
                    <th>Sucursal</th>
                    <th>Usuario</th>
                    <th>Referencia</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movements as $m)
                    <tr>
                        <td style="color: var(--text-muted); white-space: nowrap;">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                        <td style="color: var(--text); font-weight: 600;">{{ $m->concept }}</td>
                        <td>
                            @if($m->cash_box === 'petty')
                                <span class="cm-tag" style="color:#a78bfa; border-color:rgba(167,139,250,0.4);">CAJA CHICA</span>
                            @elseif($m->cash_box === 'transfer')
                                <span class="cm-tag" style="color:#60a5fa; border-color:rgba(96,165,250,0.4);">TRASPASO</span>
                            @else
                                <span class="cm-tag" style="color:var(--text-muted); border-color:var(--border-strong);">VENTA</span>
                            @endif
                        </td>
                        <td style="font-weight:700; color: {{ $m->type === 'income' ? '#22c55e' : '#ef4444' }};">
                            {{ $m->type === 'income' ? 'Ingreso' : 'Egreso' }}
                        </td>
                        <td style="text-align:right; font-weight:700; color: {{ $m->type === 'income' ? '#22c55e' : '#ef4444' }};">
                            {{ $m->type === 'income' ? '+' : '-' }} Bs. {{ number_format($m->amount, 2) }}
                        </td>
                        <td>{{ $m->cashSession->branch->name ?? '—' }}</td>
                        <td>{{ $m->user->name ?? 'Sistema' }}</td>
                        <td style="color: var(--text-muted);">{{ $m->reference ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="cm-empty">No hay movimientos para los filtros seleccionados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="cm-pag">
        {{ $movements->links() }}
    </div>
</div>
