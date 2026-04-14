<?php

namespace App\Modules\Tickets\Services;

use App\Modules\Orders\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class TicketService
{
    /**
     * Genera el ticket de cocina en PDF (80mm de ancho).
     *
     * @param  Order  $order  El pedido (se hace eager load internamente)
     * @return string  Ruta relativa al storage del PDF generado
     */
    public function generateKitchenTicket(Order $order): string
    {
        // Eager load todas las relaciones necesarias para el ticket
        $order->load([
            'items.productVariant.product',
            'items.sauces.sauce',
            'table',
            'branch',
        ]);

        // Generar PDF con tamaño de papel para impresora térmica 80mm
        // 226.77pt ≈ 80mm; alto generoso para que el contenido defina el corte
        $pdf = Pdf::loadView('tickets.kitchen', compact('order'))
            ->setPaper([0, 0, 226.77, 800], 'portrait');

        // Construir la ruta de almacenamiento
        $branchSlug  = $order->branch->slug;
        $orderNumber = $order->order_number;
        $path = "tickets/{$branchSlug}/kitchen/{$orderNumber}.pdf";

        // Guardar el PDF en storage/app
        Storage::put($path, $pdf->output());

        // Crear o actualizar registro en tabla tickets.
        // NOTA: La migración de la tabla 'tickets' la genera otro miembro del equipo.
        // Este código asume que la tabla existe con la estructura especificada.
        $ticketNumber = 'TK-' . $orderNumber;

        \Illuminate\Support\Facades\DB::table('tickets')->updateOrInsert(
            [
                'order_id' => $order->id,
                'type'     => 'kitchen',
            ],
            [
                'branch_id'     => $order->branch_id,
                'ticket_number' => $ticketNumber,
                'pdf_path'      => $path,
                'printed_at'    => null,
                'created_by'    => auth()->id(),
                'created_at'    => now(),
                'updated_at'    => now(),
            ]
        );

        return $path;
    }
}
