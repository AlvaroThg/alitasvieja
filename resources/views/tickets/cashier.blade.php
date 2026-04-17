<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; margin: 0; padding: 10px; color: #000; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .mb-2 { margin-bottom: 8px; }
        .mt-2 { margin-top: 8px; }
        .divider { border-bottom: 1px dashed #000; margin: 10px 0; }
        table { w-full; width: 100%; border-collapse: collapse; }
        td, th { text-align: left; vertical-align: top; }
        .text-xs { font-size: 10px; }
    </style>
</head>
<body>
    <div class="text-center mb-2">
        <h2 class="font-bold" style="margin: 0; font-size: 18px;">Alitas Vega</h2>
        <p class="text-xs" style="margin: 2px 0;">Ticket de Venta</p>
        <p class="font-bold">Orden #{{ $order->order_number }}</p>
    </div>

    <div class="divider"></div>

    <p>Fecha: {{ $order->opened_at }}</p>
    <p>Mesa: {{ $order->table ? $order->table->name : 'N/A' }}</p>

    <div class="divider"></div>

    <table>
        @foreach($order->items ?? [] as $item)
        <tr>
            <td style="width: 15%">{{ $item->quantity }}x</td>
            <td style="width: 55%">
                {{ $item->productVariant->product->name ?? 'Item' }}<br>
                <span class="text-xs">{{ $item->productVariant->name ?? '' }}</span>
            </td>
            <td class="text-right" style="width: 30%">${{ number_format($item->subtotal, 2) }}</td>
        </tr>
        @endforeach
    </table>

    <div class="divider"></div>

    <table>
        <tr>
            <td class="font-bold">Subtotal:</td>
            <td class="text-right">${{ number_format($order->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td class="font-bold">Descuento:</td>
            <td class="text-right">${{ number_format($order->discount, 2) }}</td>
        </tr>
        <tr>
            <td class="font-bold" style="font-size: 14px;">TOTAL:</td>
            <td class="text-right font-bold" style="font-size: 14px;">${{ number_format($order->total, 2) }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <div class="text-center mt-2">
        <p class="font-bold">¡Gracias por su compra!</p>
        <p class="text-xs">Vuelva pronto</p>
    </div>

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
