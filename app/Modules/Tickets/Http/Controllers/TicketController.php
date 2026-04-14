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
            ->header('Content-Disposition', 'inline; filename="' . $order->order_number . '.pdf"');
    }
}
