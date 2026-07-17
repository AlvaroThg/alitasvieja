<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; margin: 0; padding: 0; color: #000; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .mb-2 { margin-bottom: 4px; }
        .mt-2 { margin-top: 4px; }
        .divider { border-bottom: 1px dashed #000; margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; margin: 0; padding: 0; }
        td, th { text-align: left; vertical-align: top; padding: 0; margin: 0; }
        p { margin: 0; padding: 0; }
        .text-xs { font-size: 9px; }
    </style>
</head>
<body>
    <div class="text-center mb-2">
        <h2 class="font-bold" style="margin: 0; font-size: 16px;">Alitas de la Vieja</h2>
        <p class="text-xs" style="margin: 2px 0;">Ticket de Venta</p>
        <p class="font-bold" style="font-size: 16px;">Turno: #{{ $order->daily_number }}</p>
        <p class="text-xs">Orden {{ $order->order_number }}</p>
        @if($order->order_type !== 'dine_in')
            <p class="font-bold" style="font-size: 14px; margin: 4px 0; border: 1px solid #000; display: inline-block; padding: 2px 5px;">
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

    <table>
        @foreach($order->items ?? [] as $item)
        <tr>
            <td style="width: 15%">{{ $item->quantity }}x</td>
            <td style="width: 55%">
                {{ $item->productVariant->product->name ?? 'Item' }}<br>
                <span class="text-xs">{{ $item->productVariant->name ?? '' }}</span>
                @if($item->notes)
                    <br><span class="text-xs" style="font-style: italic;">* {{ $item->notes }}</span>
                @endif
            </td>
            <td class="text-right" style="width: 30%">Bs. {{ number_format($item->subtotal, 2) }}</td>
        </tr>
        @endforeach
    </table>

    <div class="divider"></div>

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
            <td class="font-bold" style="font-size: 14px;">TOTAL:</td>
            <td class="text-right font-bold" style="font-size: 14px;">Bs. {{ number_format($order->total, 2) }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <div class="text-center mt-2" style="margin-bottom: 20px;">
        <p class="font-bold">¡Gracias por su compra!</p>
        <p class="text-xs">Vuelva pronto</p>
    </div>

    <!-- SEPARADOR PARA CORTAR -->
    <div style="page-break-before: always;"></div>

    <!-- TICKET DE COCINA -->
    <div class="text-center mb-2">
        <h2 class="font-bold" style="margin: 0; font-size: 16px;">*** COCINA ***</h2>
        <p class="font-bold" style="font-size: 16px;">Pedido: #{{ $order->daily_number }}</p>
        @if($order->order_type !== 'dine_in')
            <p class="font-bold" style="font-size: 14px; margin: 4px 0; border: 1px solid #000; display: inline-block; padding: 2px 5px;">
                {{ $order->order_type === 'delivery' ? 'DELIVERY' : 'PARA RECOGER' }}
            </p>
        @endif
        <p class="text-xs">Mesa: {{ $order->table ? $order->table->name : 'N/A' }} | Hora: {{ $order->opened_at ? $order->opened_at->format('H:i') : '' }}</p>
    </div>

    <div class="divider"></div>

    @foreach($order->items ?? [] as $index => $item)
        <div style="margin-bottom: 6px;">
            <div class="font-bold" style="font-size: 14px;">
                {{ $item->quantity }}x {{ $item->productVariant->product->name ?? 'Producto' }} ({{ $item->productVariant->name ?? '' }})
            </div>

            {{-- Salsas del ítem (solo si tiene) --}}
            @if($item->sauces && $item->sauces->isNotEmpty())
                @foreach($item->sauces as $sauce)
                    @if($sauce->is_coated && $sauce->quantity > 0)
                        <div style="padding-left: 10px; font-size: 12px;">
                            - {{ $sauce->quantity }}pz {{ $sauce->sauce->name ?? 'Salsa' }} [bañada]
                        </div>
                    @elseif(!$sauce->is_coated)
                        <div style="padding-left: 10px; font-size: 12px;">
                            - {{ $sauce->sauce->name ?? 'Salsa' }} [aparte]
                        </div>
                    @endif
                @endforeach
            @endif

            {{-- Notas del ítem --}}
            @if($item->notes)
                <div style="padding-left: 10px; font-size: 12px; font-style: italic;">
                    * Nota: {{ $item->notes }}
                </div>
            @endif
        </div>
        @if(!$loop->last)
            <div class="divider" style="border-top: 1px dotted #999;"></div>
        @endif
    @endforeach

    @if($order->notes)
        <div class="divider"></div>
        <p class="font-bold">Observaciones Generales de Orden:</p>
        <p class="text-xs" style="font-style: italic;">{{ $order->notes }}</p>
    @endif

    <div class="divider"></div>

    <script>
        // Si el POS necesita imprimir dinámicamente:
        window.onload = function() {
            window.print();
            window.onafterprint = function() {
                window.close();
            };
        };
    </script>
</body>
</html>
