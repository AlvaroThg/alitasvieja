<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 0; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 14px;
            margin: 0;
            padding: 6px 6px 6px 10px;
            color: #000;
            width: 226.77pt;
            line-height: 1.3;
        }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .divider { border-bottom: 1px dashed #000; margin: 4px 0; }
        p { margin: 0; padding: 0; }
        .text-xs { font-size: 11px; }
    </style>
</head>
<body>
    {{-- ═══ ENCABEZADO ═══ --}}
    <div class="text-center">
        <p class="font-bold" style="font-size: 14px;">
            {{ $order->branch->city ?? $order->branch->name }}
        </p>
        <p class="font-bold" style="font-size: 18px; margin: 4px 0;">
            *** COCINA ***
        </p>
    </div>

    <div class="divider"></div>

    {{-- ═══ NÚMERO DE PEDIDO ═══ --}}
    <div class="text-center">
        <p class="font-bold" style="font-size: 22px; margin: 4px 0;">
            Pedido #{{ $order->daily_number }}
        </p>
    </div>

    @if($order->order_type !== 'dine_in')
        <div class="text-center">
            <p class="font-bold" style="font-size: 20px; margin: 4px 0; border: 2px solid #000; padding: 4px; display: inline-block;">
                {{ $order->order_type === 'delivery' ? 'DELIVERY' : 'PARA RECOGER' }}
            </p>
        </div>
    @endif

    <div class="text-center">
        <p class="text-xs" style="color: #666;">Ref: {{ $order->order_number }}</p>
    </div>

    @if($order->table)
        <p><span class="font-bold">Mesa:</span> {{ $order->table->name }}</p>
    @endif
    <p><span class="font-bold">Hora:</span> {{ $order->opened_at ? $order->opened_at->format('H:i') : '' }}</p>

    <div class="divider"></div>

    {{-- ═══ ÍTEMS ═══ --}}
    @foreach($order->items as $index => $item)
        <div style="margin-bottom: 4px;">
            <p class="font-bold" style="font-size: 24px; line-height: 1.2; margin-bottom: 2px;">
                {{ $item->quantity }}x {{ $item->productVariant->name ?? 'Producto' }}
            </p>

            {{-- Salsas del ítem --}}
            @if($item->sauces && $item->sauces->isNotEmpty())
                @foreach($item->sauces as $sauce)
                    @if($sauce->is_coated && $sauce->quantity > 0)
                        <p class="font-bold" style="font-size: 22px; padding-left: 10px; line-height: 1.2;">
                            - {{ $sauce->quantity }} {{ $sauce->quantity == 1 ? 'alita' : 'alitas' }} con {{ $sauce->sauce->name ?? 'Salsa' }} [bañada]
                        </p>
                    @elseif(!$sauce->is_coated && $sauce->quantity > 0)
                        <p class="font-bold" style="font-size: 22px; padding-left: 10px; line-height: 1.2;">
                            - {{ $sauce->quantity }}pz {{ $sauce->sauce->name ?? 'Salsa' }} [aparte]
                        </p>
                    @endif
                @endforeach
            @endif

            {{-- Notas del ítem --}}
            @if($item->notes)
                <p style="padding-left: 10px; font-style: italic; font-size: 13px;">
                    * {{ $item->notes }}
                </p>
            @endif
        </div>

        {{-- Separador entre ítems --}}
        @if(!$loop->last)
            <div class="divider"></div>
        @endif
    @endforeach

    {{-- ═══ PIE ═══ --}}
    @if($order->notes)
        <div class="divider"></div>
        <p class="font-bold">Obs. pedido:</p>
        <p style="font-style: italic; font-size: 13px;">{{ $order->notes }}</p>
    @endif

    <div class="divider" style="margin-top: 6px;"></div>
</body>
</html>
