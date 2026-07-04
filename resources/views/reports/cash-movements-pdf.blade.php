<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1a1a1a; padding: 18px; }
        .head { border-bottom: 2px solid #dc2626; padding-bottom: 8px; margin-bottom: 12px; }
        .head h1 { font-size: 16px; color: #b91c1c; }
        .head .sub { font-size: 10px; color: #555; margin-top: 3px; }
        .totals { margin-bottom: 12px; }
        .totals span { display: inline-block; margin-right: 18px; font-size: 10.5px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f3f3f3; text-align: left; padding: 5px 6px; font-size: 9px; text-transform: uppercase; color: #444; border-bottom: 1px solid #ccc; }
        td { padding: 5px 6px; border-bottom: 1px solid #eee; font-size: 9.5px; }
        .r { text-align: right; }
        .muted { color: #777; }
        .income { color: #15803d; font-weight: bold; }
        .expense { color: #b91c1c; font-weight: bold; }
        .foot { margin-top: 12px; font-size: 8px; color: #999; text-align: right; }
        .empty { text-align: center; padding: 20px; color: #999; }
    </style>
</head>
<body>
    @php
        $cajaLabel = ['sales' => 'Caja de Venta', 'petty' => 'Caja Chica', 'transfer' => 'Traspaso'];
    @endphp
    <div class="head">
        <h1>Alitas La Vieja — Movimientos de Caja</h1>
        <div class="sub">
            Sucursal: <strong>{{ $branchName }}</strong>
            &nbsp;|&nbsp; Período: <strong>{{ $from ?: '—' }}</strong> a <strong>{{ $to ?: '—' }}</strong>
            &nbsp;|&nbsp; Total registros: <strong>{{ $movements->count() }}</strong>
        </div>
    </div>

    <div class="totals">
        <span>Total Ingresos: <strong class="income">Bs. {{ number_format($totalIncome, 2) }}</strong></span>
        <span>Total Egresos: <strong class="expense">Bs. {{ number_format($totalExpense, 2) }}</strong></span>
        <span>Balance: <strong>Bs. {{ number_format($totalIncome - $totalExpense, 2) }}</strong></span>
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Concepto</th>
                <th>Caja</th>
                <th>Tipo</th>
                <th class="r">Monto (Bs.)</th>
                <th>Sucursal</th>
                <th>Usuario</th>
                <th>Referencia</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movements as $m)
                <tr>
                    <td>{{ $m->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $m->concept }}</td>
                    <td class="muted">{{ $cajaLabel[$m->cash_box] ?? $m->cash_box }}</td>
                    <td class="{{ $m->type === 'income' ? 'income' : 'expense' }}">{{ $m->type === 'income' ? 'Ingreso' : 'Egreso' }}</td>
                    <td class="r {{ $m->type === 'income' ? 'income' : 'expense' }}">{{ $m->type === 'income' ? '+' : '-' }} {{ number_format($m->amount, 2) }}</td>
                    <td>{{ $m->cashSession->branch->name ?? '—' }}</td>
                    <td>{{ $m->user->name ?? 'Sistema' }}</td>
                    <td class="muted">{{ $m->reference ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="empty">No hay movimientos para los filtros seleccionados.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="foot">Generado el {{ now()->format('d/m/Y H:i') }}</div>
</body>
</html>
