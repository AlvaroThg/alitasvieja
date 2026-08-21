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
        $order->load([
            'items.productVariant.product',
            'items.sauces.sauce',
            'table',
            'branch',
        ]);

        // Mismo tamaño de papel que el ticket de venta: 80mm = 226.77pt
        $customPaper = array(0, 0, 226.77, 1000);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('tickets.kitchen', compact('order'))
            ->setPaper($customPaper, 'portrait');

        return $pdf->stream('cocina_' . $order->order_number . '.pdf', ['Attachment' => false]);
    }

    /**
     * GET /pos/tickets/{order}/cashier
     * Genera el ticket final para el cliente (solo venta, sin cocina).
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
