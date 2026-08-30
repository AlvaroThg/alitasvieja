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
        $cajaLabel = [
            'sales'         => 'Caja de Venta (Efectivo)',
            'petty'         => 'Caja Chica',
            'transfer'      => 'Traspaso',
            'qr'            => 'Pago QR',
            'card'          => 'Tarjeta',
            'transfer_bank' => 'Transferencia Bancaria',
        ];
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
        <span>Sobrante/Faltante: <strong class="{{ $totalDifference < 0 ? 'expense' : 'income' }}">Bs. {{ number_format($totalDifference, 2) }}</strong></span>
    </div>

    <div class="totals" style="border-top: 1px solid #ddd; padding-top: 6px;">
        <span>Ventas del período: <strong>Bs. {{ number_format($sales, 2) }}</strong></span>
        <span>Gastos: <strong class="expense">Bs. {{ number_format($realExpenses, 2) }}</strong></span>
        <span>INGRESO NETO: <strong class="{{ $netIncome < 0 ? 'expense' : 'income' }}" style="font-size: 12px;">Bs. {{ number_format($netIncome, 2) }}</strong></span>
        <span class="muted" style="font-size: 8.5px;">(ventas de todos los métodos de pago menos gastos; los traspasos internos no cuentan)</span>
    </div>

    @if($sessions->isNotEmpty())
        <div style="font-size: 11px; font-weight: bold; margin-bottom: 4px;">Cierres de caja del período</div>
        <table style="margin-bottom: 14px;">
            <thead>
                <tr>
                    <th>Cierre</th>
                    <th>Sucursal</th>
                    <th>Cerró</th>
                    <th class="r">Inicial</th>
                    <th class="r">Esperado</th>
                    <th class="r">Contado</th>
                    <th class="r">Diferencia</th>
                    <th>Notas</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sessions as $s)
                    @php $dif = (float) $s->difference; @endphp
                    <tr>
                        <td>{{ $s->closed_at?->format('d/m/Y H:i') }}</td>
                        <td>{{ $s->branch->name ?? '—' }}</td>
                        <td>{{ $s->closedBy->name ?? '—' }}</td>
                        <td class="r muted">{{ number_format($s->opening_amount, 2) }}</td>
                        <td class="r">{{ number_format($s->expected_amount, 2) }}</td>
                        <td class="r"><strong>{{ number_format($s->closing_amount, 2) }}</strong></td>
                        <td class="r {{ $dif < 0 ? 'expense' : 'income' }}">{{ $dif > 0 ? '+' : '' }}{{ number_format($dif, 2) }}</td>
                        <td class="muted">{{ $s->notes ?: '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="font-size: 11px; font-weight: bold; margin-bottom: 4px;">Movimientos</div>
    @endif

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
