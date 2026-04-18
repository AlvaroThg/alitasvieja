<?php

namespace App\Modules\Orders\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Orders\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class KitchenController extends Controller
{
    /**
     * GET /kitchen/orders
     * Pedidos del día con status 'open' o 'ready' del branch activo.
     * Ordenados por daily_number ASC para cocina.
     */
    public function index(): JsonResponse
    {
        $branchId = auth()->user()->activeBranchId();

        if (!$branchId) {
            return response()->json(['error' => 'No hay sucursal activa.'], 422);
        }

        $orders = Order::byBranch($branchId)
            ->today()
            ->whereIn('status', ['open', 'ready'])
            ->with([
                'items.productVariant',
                'items.sauces.sauce',
                'table',
            ])
            ->orderBy('daily_number', 'asc')
            ->get()
            ->map(function ($order) {
                // Añadir daily_label al JSON
                $order->append('daily_label');
                return $order;
            });

        return response()->json(['data' => $orders]);
    }

    /**
     * PATCH /kitchen/orders/{order}/ready
     * Marca un pedido como 'ready' (listo para servir).
     */
    public function markReady(Order $order): JsonResponse
    {
        if ($order->status !== 'open') {
            throw ValidationException::withMessages([
                'status' => 'Solo se pueden marcar como listos los pedidos en estado abierto.',
            ]);
        }

        $order->update(['status' => 'ready']);

        return response()->json([
            'order_id'     => $order->id,
            'status'       => $order->status,
            'daily_number' => $order->daily_number,
            'daily_label'  => $order->daily_label,
        ]);
    }
}
