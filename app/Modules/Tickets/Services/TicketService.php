<?php

namespace App\Modules\Tickets\Services;

use App\Modules\Orders\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class TicketService
{
    /**
     * Genera el ticket de cocina en PDF (80mm) para un pedido.
     *
     * @param  Order   $order  Debe tener eager loaded: items.productVariant.product, items.sauces.sauce, table, branch
     * @param  int|null $createdBy  ID del usuario que genera el ticket
     * @return string  Ruta relativa al storage del PDF generado
     */
    public function generateKitchenTicket(Order $order, ?int $createdBy = null): string
    {
        // Eager load si no se cargaron previamente
        $order->loadMissing([
            'items.productVariant.product',
            'items.sauces.sauce',
            'table',
            'branch',
        ]);

        $branchSlug  = $order->branch->slug;
        $orderNumber = $order->order_number;

        // Generar el PDF con tamaño de ticket térmico (80mm de ancho)
        $pdf = Pdf::loadView('tickets.kitchen', compact('order'))
            ->setPaper([0, 0, 226.77, 800], 'portrait');

        // Ruta de almacenamiento
        $relativePath = "tickets/{$branchSlug}/kitchen/{$orderNumber}.pdf";

        // Guardar el PDF en disco
        Storage::put($relativePath, $pdf->output());

        // Crear o actualizar registro en la tabla tickets
        // NOTA: No se genera la migración de tickets (la hace otro dev).
        //       Este código asume que la tabla ya existe.
        $ticketNumber = 'TK-' . $orderNumber;

        DB::table('tickets')->updateOrInsert(
            [
                'order_id' => $order->id,
                'type'     => 'kitchen',
            ],
            [
                'branch_id'     => $order->branch_id,
                'ticket_number' => $ticketNumber,
                'pdf_path'      => $relativePath,
                'printed_at'    => null,
                'created_by'    => $createdBy ?? auth()->id(),
                'created_at'    => now(),
                'updated_at'    => now(),
            ]
        );

        return $relativePath;
    }
}
