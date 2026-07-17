<?php

namespace App\Modules\Promotions\Services;

use App\Modules\Orders\Models\Order;
use App\Modules\Promotions\Models\OrderPromotion;
use App\Modules\Promotions\Models\Promotion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PromotionEngine
{
    /**
     * Retorna promociones aplicables a un pedido.
     */
    public function getAvailablePromotions(Order $order): Collection
    {
        $promotions = Promotion::active()
            ->forBranch($order->branch_id)
            ->get();

        return $promotions->filter(function (Promotion $promotion) use ($order) {
            return $promotion->isApplicable($order);
        })->values();
    }

    /**
     * Aplica una promoción a un pedido.
     *
     * @throws ValidationException
     */
    public function apply(Order $order, int $promotionId, ?string $notes = null): OrderPromotion
    {
        // Validar que el pedido esté abierto
        if ($order->status !== 'open') {
            throw ValidationException::withMessages([
                'order' => 'Solo se pueden aplicar promociones a pedidos abiertos.',
            ]);
        }

        // REGLA 1: Una promoción por pedido
        if (OrderPromotion::where('order_id', $order->id)->exists()) {
            throw ValidationException::withMessages([
                'promotion' => 'Este pedido ya tiene una promoción aplicada.',
            ]);
        }

        $promotion = Promotion::active()
            ->forBranch($order->branch_id)
            ->find($promotionId);

        if (!$promotion) {
            throw ValidationException::withMessages([
                'promotion' => 'La promoción no existe, no está activa o no aplica a esta sucursal.',
            ]);
        }

        if (!$promotion->isApplicable($order)) {
            throw ValidationException::withMessages([
                'promotion' => 'Esta promoción no cumple las condiciones para ser aplicada al pedido.',
            ]);
        }

        // REGLA 3: Cálculo del descuento
        $discountApplied = 0.0;
        $freeItemVariantId = null;
        $freeItemQuantity = null;

        $subtotal = (float) $order->subtotal;

        if ($promotion->discount_type === 'percentage') {
            $discountApplied = round($subtotal * ((float) $promotion->discount_value / 100), 2);
            // Evitar descuentos mayores al subtotal
            if ($discountApplied > $subtotal) {
                $discountApplied = $subtotal;
            }
        } elseif ($promotion->discount_type === 'fixed') {
            $discountApplied = min((float) $promotion->discount_value, $subtotal);
        } elseif ($promotion->discount_type === 'free_item') {
            $discountApplied = 0.0;
            $freeItemVariantId = $promotion->free_product_variant_id;
            $freeItemQuantity = $promotion->free_quantity;
        }

        return DB::transaction(function () use ($order, $promotion, $discountApplied, $freeItemVariantId, $freeItemQuantity, $notes, $subtotal) {
            $orderPromotion = OrderPromotion::create([
                'order_id'             => $order->id,
                'promotion_id'         => $promotion->id,
                'discount_applied'     => $discountApplied,
                'free_item_variant_id' => $freeItemVariantId,
                'free_item_quantity'   => $freeItemQuantity,
                'notes'                => $notes,
            ]);

            // Actualizar totales del pedido
            $order->update([
                'discount' => $discountApplied,
                'total'    => $subtotal - $discountApplied,
            ]);

            return $orderPromotion;
        });
    }

    /**
     * Remueve la promoción de un pedido.
     *
     * @throws ValidationException
     */
    public function remove(Order $order): void
    {
        if ($order->status !== 'open') {
            throw ValidationException::withMessages([
                'order' => 'Solo se pueden remover promociones de pedidos abiertos.',
            ]);
        }

        DB::transaction(function () use ($order) {
            OrderPromotion::where('order_id', $order->id)->delete();

            $order->update([
                'discount' => 0.0,
                'total'    => $order->subtotal,
            ]);
        });
    }
}
