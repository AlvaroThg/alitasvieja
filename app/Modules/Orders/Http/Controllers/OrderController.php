<?php

namespace App\Modules\Orders\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Models\OrderItem;
use App\Modules\Orders\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * POST /pos/orders
     * Crea un nuevo pedido.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => 'required|integer|exists:branches,id',
            'table_id'  => 'nullable|integer|exists:tables,id',
            'notes'     => 'nullable|string|max:500',
        ]);

        try {
            $order = $this->orderService->createOrder(
                $validated['branch_id'],
                $validated['table_id'] ?? null,
                $request->user()->id,
                $validated['notes'] ?? null
            );

            return response()->json([
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
                'status'       => $order->status,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * POST /pos/orders/{order}/items
     * Agrega un ítem al pedido.
     */
    public function addItem(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'product_variant_id'   => 'required|integer|exists:product_variants,id',
            'quantity'             => 'required|integer|min:1',
            'notes'                => 'nullable|string|max:500',
            'sauces'               => 'nullable|array',
            'sauces.*.sauce_id'    => 'required_with:sauces|integer|exists:sauces,id',
            'sauces.*.quantity'    => 'required_with:sauces|integer|min:0',
            'sauces.*.is_coated'   => 'required_with:sauces|boolean',
        ]);

        try {
            $orderItem = $this->orderService->addItem($order, $validated);

            // Refrescar el pedido para obtener el total actualizado
            $order->refresh();

            return response()->json([
                'item_id'            => $orderItem->id,
                'subtotal'           => $orderItem->subtotal,
                'order_total'        => $order->total,
                'extra_sauce_charge' => $orderItem->extra_sauce_charge,
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * DELETE /pos/orders/{order}/items/{item}
     * Elimina un ítem del pedido.
     */
    public function removeItem(Order $order, OrderItem $item): JsonResponse
    {
        // Verificar que el ítem pertenece al pedido
        if ($item->order_id !== $order->id) {
            abort(404, 'El ítem no pertenece a este pedido.');
        }

        try {
            $this->orderService->removeItem($item);

            $order->refresh();

            return response()->json([
                'order_total' => $order->total,
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * GET /pos/orders/{order}
     * Muestra el detalle completo del pedido.
     */
    public function show(Order $order): JsonResponse
    {
        $order->load([
            'items.productVariant.product',
            'items.sauces.sauce',
            'branch',
            'table',
            'user',
            'payments',
        ]);

        return response()->json($order);
    }
}
