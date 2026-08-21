<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 0; }
        * { box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; margin: 0; padding: 2mm 2mm 2mm 4mm; color: #000; width: 72mm; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .mb-2 { margin-bottom: 4px; }
        .mt-2 { margin-top: 4px; }
        .divider { border-bottom: 1px dashed #000; margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; margin: 0; padding: 0; }
        td, th { text-align: left; vertical-align: top; padding: 0; margin: 0; }
        p { margin: 0; padding: 0; }
        .text-xs { font-size: 12px; }
    </style>
</head>
<body>
    {{-- ═══ ENCABEZADO ═══ --}}
    <div class="text-center mb-2">
        <h2 class="font-bold" style="margin: 0; font-size: 20px;">Alitas de la Vieja</h2>
        <p class="text-xs" style="margin: 2px 0;">Ticket de Venta</p>
        <p class="font-bold" style="font-size: 18px;">Turno: #{{ $order->daily_number }}</p>
        <p class="text-xs">Orden {{ $order->order_number }}</p>
        @if($order->order_type !== 'dine_in')
            <p class="font-bold" style="font-size: 16px; margin: 4px 0; border: 1px solid #000; display: inline-block; padding: 2px 5px;">
                {{ $order->order_type === 'delivery' ? 'DELIVERY' : 'PARA RECOGER' }}
            </p>
        @endif
    </div>

    <div class="divider"></div>

    <p>Fecha: {{ $order->opened_at }}</p>
    <p>Mesa: {{ $order->table ? $order->table->name : 'N/A' }}</p>

    @if($order->notes)
        <div class="divider"></div>
        <p class="font-bold">Observaciones Generales:</p>
        <p class="text-xs" style="font-style: italic;">{{ $order->notes }}</p>
    @endif

    <div class="divider"></div>

    {{-- ═══ ÍTEMS ═══ --}}
    <table>
        @foreach($order->items ?? [] as $item)
        <tr>
            <td style="width: 15%">{{ $item->quantity }}x</td>
            <td style="width: 55%">
                {{ $item->productVariant->product->name ?? 'Item' }}<br>
                <span class="text-xs">{{ $item->productVariant->name ?? '' }}</span>
                @if($item->sauces && $item->sauces->isNotEmpty())
                    @foreach($item->sauces as $sauce)
                        <br><span class="text-xs" style="color: #4b5563;">- {{ $sauce->quantity }}x {{ $sauce->sauce->name ?? 'Salsa' }} [{{ $sauce->is_coated ? 'bañada' : 'aparte' }}]</span>
                    @endforeach
                @endif
                @if($item->notes)
                    <br><span class="text-xs" style="font-style: italic;">* {{ $item->notes }}</span>
                @endif
            </td>
            <td class="text-right" style="width: 30%">Bs. {{ number_format($item->subtotal, 2) }}</td>
        </tr>
        @endforeach
    </table>

    <div class="divider"></div>

    {{-- ═══ TOTALES ═══ --}}
    <table>
        <tr>
            <td class="font-bold">Subtotal:</td>
            <td class="text-right">Bs. {{ number_format($order->subtotal, 2) }}</td>
        </tr>
        @if($order->discount > 0)
        <tr>
            <td class="font-bold">
                Descuento
                @if($order->appliedPromotion && $order->appliedPromotion->promotion)
                    <br><span class="text-xs" style="font-weight: normal; font-style: italic;">({{ $order->appliedPromotion->promotion->name }})</span>
                @endif
            </td>
            <td class="text-right">-Bs. {{ number_format($order->discount, 2) }}</td>
        </tr>
        @endif
        <tr>
            <td class="font-bold" style="font-size: 16px;">TOTAL:</td>
            <td class="text-right font-bold" style="font-size: 16px;">Bs. {{ number_format($order->total, 2) }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    {{-- ═══ PIE ═══ --}}
    <div class="text-center mt-2" style="margin-bottom: 20px;">
        <p class="font-bold">¡Gracias por su compra!</p>
        <p class="text-xs">Vuelva pronto</p>
    </div>
</body>
</html>
