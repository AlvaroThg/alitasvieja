<?php

namespace App\Modules\Tickets\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Orders\Models\Order;
use App\Modules\Tickets\Services\TicketService;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    protected TicketService $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    /**
     * GET /pos/tickets/{order}/kitchen
     * Genera y retorna el PDF del ticket de cocina inline (previsualización en navegador).
     */
    public function kitchen(Order $order)
    {
        $path = $this->ticketService->generateKitchenTicket($order);

        return response(Storage::get($path), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="kitchen_' . $order->order_number . '.pdf"');
    }

    /**
     * GET /pos/tickets/{order}/cashier
     * Genera el ticket final para el cliente y activa impresión de 80mm
     */
    public function cashier(Order $order)
    {
        $order->load([
            'items.productVariant.product',
            'items.sauces.sauce',
            'table',
            'branch',
            'appliedPromotion.promotion'
        ]);

        // Setup dinámico de hoja 80mm (226.77 pt) con longitud auto-expandible (1000 pt de margen)
        $customPaper = array(0, 0, 226.77, 1000); 
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('tickets.cashier', compact('order'))
            ->setPaper($customPaper, 'portrait');

        return $pdf->stream('ticket_' . $order->order_number . '.pdf', ['Attachment' => false]);
    }
}
