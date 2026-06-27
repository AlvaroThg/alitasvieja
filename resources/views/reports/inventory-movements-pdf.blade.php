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
        table { width: 100%; border-collapse: collapse; }
        th { background: #f3f3f3; text-align: left; padding: 5px 6px; font-size: 9px; text-transform: uppercase; color: #444; border-bottom: 1px solid #ccc; }
        td { padding: 5px 6px; border-bottom: 1px solid #eee; font-size: 9.5px; }
        .r { text-align: right; }
        .muted { color: #777; }
        .tag { font-weight: bold; }
        .foot { margin-top: 12px; font-size: 8px; color: #999; text-align: right; }
        .empty { text-align: center; padding: 20px; color: #999; }
    </style>
</head>
<body>
    @php
        $tipoMov = ['in' => 'Entrada', 'out' => 'Salida', 'adjustment' => 'Ajuste', 'sale' => 'Venta'];
    @endphp
    <div class="head">
        <h1>Alitas La Vieja — Historial de Movimientos de Inventario</h1>
        <div class="sub">
            Sucursal: <strong>{{ $branchName }}</strong>
            &nbsp;|&nbsp; Período: <strong>{{ $from ?: '—' }}</strong> a <strong>{{ $to ?: '—' }}</strong>
            &nbsp;|&nbsp; Total registros: <strong>{{ $movements->count() }}</strong>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Producto</th>
                <th>Variante</th>
                <th>Tipo</th>
                <th class="r">Cantidad</th>
                <th class="r">Stock (antes → después)</th>
                <th>Sucursal</th>
                <th>Usuario</th>
                <th>Motivo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movements as $m)
                <tr>
                    <td>{{ $m->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $m->productVariant->product->name ?? '—' }}</td>
                    <td class="muted">{{ $m->productVariant->name ?? '' }}</td>
                    <td class="tag">{{ $tipoMov[$m->type] ?? $m->type }}</td>
                    <td class="r">{{ $m->quantity }}</td>
                    <td class="r muted">{{ $m->stock_before }} → {{ $m->stock_after }}</td>
                    <td>{{ $m->branch->name ?? '—' }}</td>
                    <td>{{ $m->user->name ?? 'Sistema' }}</td>
                    <td class="muted">{{ $m->reason ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="empty">No hay movimientos para los filtros seleccionados.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="foot">Generado el {{ now()->format('d/m/Y H:i') }}</div>
</body>
</html>
