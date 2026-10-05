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

    @if($order->customer_name)
        <div class="text-center" style="margin: 6px 0;">
            <p class="font-bold" style="font-size: 26px; line-height: 1.2; border: 2px dashed #000; padding: 4px;">
                CLIENTE: {{ is_array($order->customer_name) ? implode(', ', $order->customer_name) : $order->customer_name }}
            </p>
        </div>
    @endif

    <div class="text-center">
        <p class="text-xs" style="color: #666;">Ref: {{ $order->order_number }}</p>
    </div>

    @if($order->table)
        <p><span class="font-bold">Mesa:</span> {{ is_array($order->table->name ?? null) ? implode(', ', $order->table->name) : ($order->table->name ?? '') }}</p>
    @endif
    <p><span class="font-bold">Hora:</span> {{ $order->opened_at ? $order->opened_at->format('H:i') : '' }}</p>

    <div class="divider"></div>

    {{-- ═══ ÍTEMS ═══ --}}
    @foreach($order->items as $index => $item)
        @php
            $itemQty = max(1, (int) $item->quantity);
            $hasSauces = $item->sauces && $item->sauces->isNotEmpty();
            $isLastItem = $loop->last;
            $isWings = (bool) ($item->productVariant?->product?->is_wings);
            $wingsPerUnit = $isWings ? (int) ($item->productVariant?->wings_count ?? 0) : 1;

            // Construir la distribución por porción para el ticket de cocina
            $portionSaucesMap = [];
            if (is_array($item->unit_sauces ?? null) && !empty($item->unit_sauces)) {
                $portionSaucesMap = $item->unit_sauces;
            } elseif ($hasSauces) {
                $remainingCoated = [];
                $remainingSide = [];

                foreach ($item->sauces as $sRow) {
                    $name = $sRow->sauce->name ?? 'Salsa';
                    $q = (int) $sRow->quantity;
                    if ($sRow->is_coated) {
                        if ($q > 0) $remainingCoated[] = ['name' => $name, 'qty' => $q];
                    } else {
                        if ($q > 0) $remainingSide[] = ['name' => $name, 'qty' => $q];
                    }
                }

                for ($u = 0; $u < $itemQty; $u++) {
                    $portionSaucesMap[$u] = [];
                    $needed = $wingsPerUnit > 0 ? $wingsPerUnit : 1;

                    // Asignar bañadas
                    foreach ($remainingCoated as &$cRow) {
                        if ($needed <= 0 && $wingsPerUnit > 0) break;
                        if ($cRow['qty'] > 0) {
                            $take = $wingsPerUnit > 0 ? min($needed, $cRow['qty']) : $cRow['qty'];
                            $portionSaucesMap[$u][] = [
                                'name' => $cRow['name'],
                                'qty' => $take,
                                'is_coated' => true,
                            ];
                            $cRow['qty'] -= $take;
                            if ($wingsPerUnit > 0) {
                                $needed -= $take;
                            }
                        }
                    }
                    unset($cRow);

                    // Asignar aparte
                    foreach ($remainingSide as &$sRow) {
                        if ($needed <= 0 && $wingsPerUnit > 0) break;
                        if ($sRow['qty'] > 0) {
                            $take = $wingsPerUnit > 0 ? min($needed, $sRow['qty']) : $sRow['qty'];
                            $portionSaucesMap[$u][] = [
                                'name' => $sRow['name'],
                                'qty' => $take,
                                'is_coated' => false,
                            ];
                            $sRow['qty'] -= $take;
                            if ($wingsPerUnit > 0) {
                                $needed -= $take;
                            }
                        }
                    }
                    unset($sRow);
                }
            }
        @endphp
        @for($unit = 0; $unit < $itemQty; $unit++)
            @php 
                $isLastUnit = ($unit === $itemQty - 1); 
                $unitSaucesList = $portionSaucesMap[$unit] ?? [];
            @endphp
            <div style="margin-bottom: 4px;">
                <p class="font-bold" style="font-size: 24px; line-height: 1.2; margin-bottom: 2px;">
                    1x {{ $item->productVariant->product->name ?? 'Producto' }}
                    @if($itemQty > 1)
                        <span style="font-size: 16px; color: #555;">({{ $unit + 1 }}/{{ $itemQty }})</span>
                    @endif
                </p>
                <p class="font-bold" style="font-size: 26px; padding-left: 10px; margin-bottom: 4px; line-height: 1.2;">
                    ({{ $item->productVariant->name ?? '' }})
                </p>

                {{-- Salsas del ítem para la porción --}}
                @if(!empty($unitSaucesList))
                    @foreach($unitSaucesList as $sEntry)
                        @php
                            $sName = is_array($sEntry['name'] ?? null) ? implode(', ', $sEntry['name']) : ($sEntry['name'] ?? 'Salsa');
                            $wQty = (int) ($sEntry['qty'] ?? 0);
                            $sQty = (int) ($sEntry['qty_side'] ?? 0);
                            if (isset($sEntry['is_coated'])) {
                                if ($sEntry['is_coated']) {
                                    $wQty = (int) ($sEntry['qty'] ?? 1);
                                    $sQty = 0;
                                } else {
                                    $wQty = 0;
                                    $sQty = (int) ($sEntry['qty'] ?? 1);
                                }
                            }
                        @endphp
                        @if($wQty > 0)
                            <p class="font-bold" style="font-size: 22px; padding-left: 10px; line-height: 1.2;">
                                - {{ $isWings ? $wQty . ' ' . ($wQty == 1 ? 'alita' : 'alitas') . ' con ' : '' }}{{ $sName }} [bañada]
                            </p>
                        @endif
                        @if($sQty > 0)
                            <p class="font-bold" style="font-size: 22px; padding-left: 10px; line-height: 1.2;">
                                - {{ $isWings ? $sQty . 'pz ' : '' }}{{ $sName }} [aparte]
                            </p>
                        @endif
                    @endforeach
                @endif

                {{-- Notas del ítem --}}
                @if($item->notes)
                    <p style="padding-left: 10px; font-style: italic; font-size: 13px;">
                        * {{ is_array($item->notes) ? implode(', ', $item->notes) : $item->notes }}
                    </p>
                @endif
            </div>

            {{-- Separador entre ítems o unidades --}}
            @if(!$isLastItem || !$isLastUnit)
                <div class="divider"></div>
            @endif
        @endfor
    @endforeach

    {{-- ═══ PIE ═══ --}}
    @if($order->notes)
        <div class="divider"></div>
        <p class="font-bold">Obs. pedido:</p>
        <p style="font-style: italic; font-size: 13px;">{{ is_array($order->notes) ? implode(', ', $order->notes) : $order->notes }}</p>
    @endif

    <div class="divider" style="margin-top: 6px;"></div>

    {{-- ═══ TOTAL ═══ --}}
    <div class="text-center" style="margin-top: 4px;">
        <p class="font-bold" style="font-size: 20px;">
            TOTAL: Bs. {{ number_format($order->total, 2) }}
        </p>
    </div>

    <div class="divider" style="margin-top: 4px;"></div>
</body>
</html>
