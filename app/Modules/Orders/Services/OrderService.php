<?php

namespace App\Modules\Orders\Services;

use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Models\OrderItem;
use App\Modules\Orders\Models\OrderItemSauce;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(
        private WingSauceValidator $sauceValidator
    ) {}

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

    /**
     * Añade un ítem al pedido con su precio snapshot y salsas opcionales.
     *
     * @param  Order  $order
     * @param  array  $itemData  [product_variant_id, quantity, notes?, sauces?]
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
            $sauces    = $itemData['sauces'] ?? [];

            // Obtener el variant con su producto para verificar is_wings
            $variant = DB::table('product_variants')
                ->join('products', 'products.id', '=', 'product_variants.product_id')
                ->where('product_variants.id', $variantId)
                ->select(
                    'product_variants.id',
                    'product_variants.wings_count',
                    'product_variants.max_sauces',
                    'products.is_wings'
                )
                ->first();

            if (!$variant) {
                throw ValidationException::withMessages([
                    'product_variant_id' => 'La variante de producto no existe.',
                ]);
            }

            // Obtener unit_price desde product_prices
            $priceRecord = DB::table('product_prices')
                ->where('product_variant_id', $variantId)
                ->where('branch_id', $order->branch_id)
                ->first();

            if (!$priceRecord) {
                throw ValidationException::withMessages([
                    'product_variant_id' => 'No se encontró precio para esta variante en la sucursal.',
                ]);
            }

            $unitPrice = (float) $priceRecord->price;
            $extraSauceCharge = 0.0;

            // Si es producto de alitas, validar salsas
            if ($variant->is_wings && !empty($sauces)) {
                // Crear un objeto anónimo compatible con el validador
                $variantObj = (object) [
                    'wings_count' => $variant->wings_count,
                    'max_sauces'  => $variant->max_sauces,
                ];

                $extraSauceCharge = $this->sauceValidator->validate(
                    $variantObj,
                    $order->branch_id,
                    $sauces
                );
            }

            // Calcular subtotal del ítem
            $subtotal = ($unitPrice * $quantity) + $extraSauceCharge;

            // Crear el OrderItem
            $item = OrderItem::create([
                'order_id'           => $order->id,
                'product_variant_id' => $variantId,
                'quantity'           => $quantity,
                'unit_price'         => $unitPrice,
                'extra_sauce_charge' => $extraSauceCharge,
                'subtotal'           => $subtotal,
                'notes'              => $notes,
            ]);

            // Crear las salsas si es producto de alitas
            if ($variant->is_wings && !empty($sauces)) {
                foreach ($sauces as $sauceData) {
                    OrderItemSauce::create([
                        'order_item_id' => $item->id,
                        'sauce_id'      => $sauceData['sauce_id'],
                        'quantity'      => $sauceData['quantity'] ?? 0,
                        'is_coated'     => $sauceData['is_coated'] ?? true,
                    ]);
                }
            }

            // Recalcular totales del pedido
            $this->recalculateOrder($order);

            return $item->load('sauces');
        });
    }

    /**
     * Recalcula subtotal y total del pedido a partir de sus ítems.
     */
    public function recalculateOrder(Order $order): void
    {
        $subtotal = OrderItem::where('order_id', $order->id)->sum('subtotal');

        $order->update([
            'subtotal' => $subtotal,
            'total'    => $subtotal - $order->discount,
        ]);
    }

    /**
     * Elimina un ítem del pedido (cascade elimina las salsas)
     * y recalcula los totales.
     */
    public function removeItem(OrderItem $item): void
    {
        $order = $item->order;

        if ($order->status !== 'open') {
            throw ValidationException::withMessages([
                'order' => 'No se pueden eliminar ítems de un pedido que no está abierto.',
            ]);
        }

        DB::transaction(function () use ($item, $order) {
            $item->delete(); // cascade elimina order_item_sauces
            $this->recalculateOrder($order);
        });
    }
}
