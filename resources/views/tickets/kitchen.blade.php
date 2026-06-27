<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            width: 72mm;
            margin: 0;
            padding: 4mm;
            line-height: 1.4;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .separator {
            text-align: center;
            letter-spacing: 0;
        }

        .item-block {
            margin-bottom: 2px;
        }

        .sauce-line {
            padding-left: 8px;
            font-size: 10px;
        }

        .notes-line {
            padding-left: 8px;
            font-size: 10px;
            font-style: italic;
        }

        .footer-notes {
            font-size: 10px;
            font-style: italic;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    {{-- ═══ ENCABEZADO ═══ --}}
    <div class="center bold">
        {{ $order->branch->city ?? $order->branch->name }}
    </div>
    <div class="center bold" style="font-size: 13px; margin: 4px 0;">
        ★ COCINA ★
    </div>
    <div class="separator">--------------------------------</div>

    {{-- MODIFICADO: daily_number prominente para cocina (OBS 1) --}}
    <div class="center bold" style="font-size: 14px; margin: 2px 0;">
        Pedido #{{ $order->daily_number }}
    </div>
    <div style="font-size: 9px; text-align: center; color: #666;">
        Ref: {{ $order->order_number }}
    </div>
    {{-- FIN MODIFICADO --}}
    <div>
        <span class="bold">Mesa:</span> {{ $order->table->number ?? 'Sin mesa' }}
    </div>
    <div>
        <span class="bold">Hora:</span> {{ $order->opened_at->format('H:i') }}
    </div>
    <div class="separator">--------------------------------</div>

    {{-- ═══ ÍTEMS ═══ --}}
    @foreach($order->items as $index => $item)
        <div class="item-block">
            <div class="bold">
                {{ $item->quantity }}x {{ $item->productVariant->name ?? 'Producto' }}
            </div>

            {{-- Salsas del ítem (solo si tiene) --}}
            @if($item->sauces->isNotEmpty())
                @foreach($item->sauces as $sauce)
                    @if($sauce->is_coated && $sauce->quantity > 0)
                        <div class="sauce-line">
                            → {{ $sauce->quantity }} {{ $sauce->quantity == 1 ? 'alita' : 'alitas' }} con {{ $sauce->sauce->name ?? 'Salsa' }}
                        </div>
                    @elseif($sauce->is_coated)
                        <div class="sauce-line">
                            → {{ $sauce->sauce->name ?? 'Salsa' }}
                        </div>
                    @else
                        <div class="sauce-line">
                            → {{ $sauce->sauce->name ?? 'Salsa' }} [aparte]
                        </div>
                    @endif
                @endforeach
            @endif

            {{-- Notas del ítem --}}
            @if($item->notes)
                <div class="notes-line">
                    * {{ $item->notes }}
                </div>
            @endif
        </div>

        {{-- Separador entre ítems (no después del último) --}}
        @if(!$loop->last)
            <div class="separator">--------------------------------</div>
        @endif
    @endforeach

    {{-- ═══ PIE ═══ --}}
    @if($order->notes)
        <div class="separator">--------------------------------</div>
        <div class="footer-notes">
            <span class="bold">Obs. pedido:</span> {{ $order->notes }}
        </div>
    @endif

    <div class="separator" style="margin-top: 6px;">--------------------------------</div>
</body>
</html>
