<?php

namespace App\Modules\Orders\Services;

use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Models\OrderItem;
use App\Modules\Orders\Models\OrderItemSauce;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    protected WingSauceValidator $sauceValidator;

    public function __construct(WingSauceValidator $sauceValidator)
    {
        $this->sauceValidator = $sauceValidator;
    }

    // ─── Crear Pedido ─────────────────────────────────────────

    /**
     * Crea un nuevo pedido en estado 'open'.
     */
    public function createOrder(int $branchId, ?int $tableId, int $userId, ?string $notes = null): Order
    {
        return DB::transaction(function () use ($branchId, $tableId, $userId, $notes) {
            $orderNumber = Order::generateOrderNumber($branchId);

            return Order::create([
                'branch_id'    => $branchId,
                'table_id'     => $tableId,
                'user_id'      => $userId,
                'order_number' => $orderNumber,
                'status'       => 'open',
                'notes'        => $notes,
                'opened_at'    => now(),
            ]);
        });
    }

    // ─── Agregar Ítem ─────────────────────────────────────────

    /**
     * Agrega un ítem al pedido.
     *
     * @param  Order  $order
     * @param  array  $itemData  [
     *     'product_variant_id' => int,
     *     'quantity'           => int,
     *     'notes'              => ?string,
     *     'sauces'             => [['sauce_id'=>int, 'quantity'=>int, 'is_coated'=>bool], ...]
     * ]
     * @return OrderItem
     *
     * @throws ValidationException
     */
    public function addItem(Order $order, array $itemData): OrderItem
    {
        if ($order->status !== 'open') {
            throw ValidationException::withMessages([
                'order' => 'No se pueden agregar ítems a un pedido que no está abierto.',
            ]);
        }

        return DB::transaction(function () use ($order, $itemData) {
            $variantId = $itemData['product_variant_id'];
            $quantity  = $itemData['quantity'] ?? 1;
            $notes     = $itemData['notes'] ?? null;
            $saucesData = $itemData['sauces'] ?? [];

            // Obtener el variant con su producto (para verificar is_wings)
            $variant = \App\Modules\Menu\Models\ProductVariant::with('product')->findOrFail($variantId);

            // Obtener precio desde product_prices para esta sucursal
            $productPrice = DB::table('product_prices')
                ->where('product_variant_id', $variantId)
                ->where('branch_id', $order->branch_id)
                ->first();

            if (!$productPrice) {
                throw ValidationException::withMessages([
                    'product_variant_id' => "No se encontró un precio configurado para este producto en esta sucursal.",
                ]);
            }

            $unitPrice = (float) $productPrice->price;
            $extraSauceCharge = 0.0;

            // Si el producto es de alitas, validar y calcular cargo de salsas
            if ($variant->product->is_wings && !empty($saucesData)) {
                $extraSauceCharge = $this->sauceValidator->validate(
                    $variant,
                    $order->branch_id,
                    $saucesData
                );
            }

            // Calcular subtotal del ítem: (unit_price × quantity) + extra_sauce_charge
            $subtotal = ($unitPrice * $quantity) + $extraSauceCharge;

            // Crear el OrderItem
            $orderItem = OrderItem::create([
                'order_id'           => $order->id,
                'product_variant_id' => $variantId,
                'quantity'           => $quantity,
                'unit_price'         => $unitPrice,
                'extra_sauce_charge' => $extraSauceCharge,
                'subtotal'           => $subtotal,
                'notes'              => $notes,
            ]);

            // Crear registros de salsas si el producto es de alitas
            if ($variant->product->is_wings && !empty($saucesData)) {
                foreach ($saucesData as $sauceEntry) {
                    OrderItemSauce::create([
                        'order_item_id' => $orderItem->id,
                        'sauce_id'      => $sauceEntry['sauce_id'],
                        'quantity'      => $sauceEntry['quantity'] ?? 0,
                        'is_coated'     => $sauceEntry['is_coated'] ?? true,
                    ]);
                }
            }

            // Recalcular totales del pedido
            $this->recalculateOrder($order);

            return $orderItem->fresh(['sauces']);
        });
    }

    // ─── Recalcular Totales ───────────────────────────────────

    /**
     * Recalcula subtotal y total del pedido a partir de sus ítems.
     */
    public function recalculateOrder(Order $order): void
    {
        $subtotal = $order->items()->sum('subtotal');
        $total    = $subtotal - (float) $order->discount;

        $order->update([
            'subtotal' => $subtotal,
            'total'    => max(0, $total), // Decisión defensiva: el total nunca es negativo
        ]);
    }

    // ─── Eliminar Ítem ────────────────────────────────────────

    /**
     * Elimina un ítem del pedido (cascade elimina las salsas).
     *
     * @throws ValidationException
     */
    public function removeItem(OrderItem $item): void
    {
        $order = $item->order;

        if ($order->status !== 'open') {
            throw ValidationException::withMessages([
                'order' => 'No se pueden eliminar ítems de un pedido que no está abierto.',
            ]);
        }

        $item->delete(); // cascade elimina order_item_sauces

        $this->recalculateOrder($order);
    }
}
