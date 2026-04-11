<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
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
            line-height: 1.3;
        }
        .center {
            text-align: center;
        }
        .bold {
            font-weight: bold;
        }
        .separator {
            text-align: center;
            letter-spacing: 1px;
        }
        .item-sauce {
            padding-left: 8px;
        }
        .item-note {
            padding-left: 8px;
            font-style: italic;
        }
        .footer-note {
            font-style: italic;
        }
    </style>
</head>
<body>
    {{-- ─── ENCABEZADO ─────────────────────────────────────── --}}
    <div class="center bold">
        {{ $order->branch->city ?? $order->branch->name }}
    </div>
    <div class="center bold">
        ★ COCINA ★
    </div>
    <div class="separator">--------------------------------</div>

    <div>
        Pedido: {{ $order->order_number }}
    </div>
    <div>
        Mesa: {{ $order->table->number ?? 'Sin mesa' }}
    </div>
    <div>
        Hora: {{ $order->opened_at->format('H:i') }}
    </div>

    <div class="separator">--------------------------------</div>

    {{-- ─── ÍTEMS ──────────────────────────────────────────── --}}
    @foreach ($order->items as $index => $item)
        <div class="bold">
            {{ $item->quantity }}x {{ $item->productVariant->name ?? 'Producto' }}
        </div>

        {{-- Salsas del ítem (solo si tiene) --}}
        @if ($item->sauces->isNotEmpty())
            @foreach ($item->sauces as $sauce)
                @if ($sauce->is_coated && $sauce->quantity > 0)
                    <div class="item-sauce">
                        → {{ $sauce->quantity }}pz {{ $sauce->sauce->name ?? 'Salsa' }} [bañada]
                    </div>
                @elseif (!$sauce->is_coated)
                    <div class="item-sauce">
                        → {{ $sauce->sauce->name ?? 'Salsa' }} [aparte]
                    </div>
                @endif
            @endforeach
        @endif

        {{-- Notas del ítem --}}
        @if ($item->notes)
            <div class="item-note">
                * {{ $item->notes }}
            </div>
        @endif

        {{-- Separador entre ítems (no después del último) --}}
        @if (!$loop->last)
            <div class="separator">--------------------------------</div>
        @endif
    @endforeach

    {{-- ─── PIE ────────────────────────────────────────────── --}}
    @if ($order->notes)
        <div class="separator">--------------------------------</div>
        <div class="footer-note">
            Obs. pedido: {{ $order->notes }}
        </div>
    @endif

    <div class="separator">--------------------------------</div>
</body>
</html>
