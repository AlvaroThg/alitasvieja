<?php

namespace App\Modules\Orders\Http\Controllers;

use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Models\OrderItem;
use App\Modules\Orders\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrderController
{
    public function __construct(
        private OrderService $orderService
    ) {}

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
                branchId: $validated['branch_id'],
                tableId:  $validated['table_id'] ?? null,
                userId:   $request->user()->id,
                notes:    $validated['notes'] ?? null,
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
     * Añade un ítem al pedido.
     */
    public function addItem(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'product_variant_id' => 'required|integer|exists:product_variants,id',
            'quantity'           => 'required|integer|min:1',
            'notes'              => 'nullable|string|max:500',
            'sauces'             => 'nullable|array',
            'sauces.*.sauce_id'  => 'required_with:sauces|integer|exists:sauces,id',
            'sauces.*.quantity'  => 'required_with:sauces|integer|min:0',
            'sauces.*.is_coated' => 'required_with:sauces|boolean',
        ]);

        try {
            $item = $this->orderService->addItem($order, $validated);

            return response()->json([
                'item_id'            => $item->id,
                'subtotal'           => $item->subtotal,
                'order_total'        => $order->fresh()->total,
                'extra_sauce_charge' => $item->extra_sauce_charge,
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
            return response()->json(['error' => 'El ítem no pertenece a este pedido.'], 403);
        }

        try {
            $this->orderService->removeItem($item);

            return response()->json([
                'order_total' => $order->fresh()->total,
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * GET /pos/orders/{order}
     * Muestra el detalle de un pedido con ítems, salsas y totales.
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

        return response()->json([
            'order' => $order,
        ]);
    }
}
