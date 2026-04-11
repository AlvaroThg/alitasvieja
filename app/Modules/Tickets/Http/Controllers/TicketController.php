<?php

namespace App\Modules\Tickets\Http\Controllers;

use App\Modules\Orders\Models\Order;
use App\Modules\Tickets\Services\TicketService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class TicketController
{
    public function __construct(
        private TicketService $ticketService
    ) {}

    /**
     * GET /pos/tickets/{order}/kitchen
     * Genera y retorna el ticket de cocina como PDF inline.
     */
    public function kitchen(Order $order): Response
    {
        $path = $this->ticketService->generateKitchenTicket($order);

        return response(Storage::get($path), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $order->order_number . '.pdf"');
    }
}
