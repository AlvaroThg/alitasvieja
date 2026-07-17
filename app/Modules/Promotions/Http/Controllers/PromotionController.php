<?php

namespace App\Modules\Promotions\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Orders\Models\Order;
use App\Modules\Promotions\Models\Promotion;
use App\Modules\Promotions\Services\PromotionEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PromotionController extends Controller
{
    protected PromotionEngine $promotionEngine;

    public function __construct(PromotionEngine $promotionEngine)
    {
        $this->promotionEngine = $promotionEngine;
    }

    /**
     * GET /admin/promotions
     * Listado de promociones (Admin)
     */
    public function index(): JsonResponse
    {
        $promotions = Promotion::with(['branch', 'freeProductVariant'])->get();
        return response()->json(['data' => $promotions]);
    }

    /**
     * POST /admin/promotions
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id'               => 'nullable|integer|exists:branches,id',
            'name'                    => 'required|string|max:150',
            'description'             => 'nullable|string',
            'type'                    => 'required|in:birthday,discount,combo,free_item,custom',
            'discount_type'           => 'required|in:percentage,fixed,free_item',
            'discount_value'          => 'nullable|numeric|min:0',
            'free_product_variant_id' => 'nullable|integer|exists:product_variants,id',
            'free_quantity'           => 'nullable|integer|min:1',
            'conditions'              => 'nullable|array',
            'starts_at'               => 'nullable|date',
            'ends_at'                 => 'nullable|date|after_or_equal:starts_at',
            'is_active'               => 'nullable|boolean',
        ]);

        if ($validated['discount_type'] === 'free_item' && empty($validated['free_product_variant_id'])) {
            return response()->json(['errors' => ['free_product_variant_id' => 'La variante gratis es requerida para el tipo de descuento free_item.']], 422);
        }

        $validated['created_by'] = $request->user()->id;
        
        $promotion = Promotion::create($validated);

        return response()->json($promotion->load(['branch', 'freeProductVariant']), 201);
    }

    /**
     * PUT /admin/promotions/{promotion}
     */
    public function update(Request $request, Promotion $promotion): JsonResponse
    {
        $validated = $request->validate([
            'branch_id'               => 'nullable|integer|exists:branches,id',
            'name'                    => 'required|string|max:150',
            'description'             => 'nullable|string',
            'type'                    => 'required|in:birthday,discount,combo,free_item,custom',
            'discount_type'           => 'required|in:percentage,fixed,free_item',
            'discount_value'          => 'nullable|numeric|min:0',
            'free_product_variant_id' => 'nullable|integer|exists:product_variants,id',
            'free_quantity'           => 'nullable|integer|min:1',
            'conditions'              => 'nullable|array',
            'starts_at'               => 'nullable|date',
            'ends_at'                 => 'nullable|date|after_or_equal:starts_at',
            'is_active'               => 'nullable|boolean',
        ]);

        if ($validated['discount_type'] === 'free_item' && empty($validated['free_product_variant_id'])) {
            return response()->json(['errors' => ['free_product_variant_id' => 'La variante gratis es requerida para el tipo de descuento free_item.']], 422);
        }

        $promotion->update($validated);

        return response()->json($promotion->fresh(['branch', 'freeProductVariant']));
    }

    /**
     * PATCH /admin/promotions/{promotion}/toggle-active
     */
    public function toggleActive(Promotion $promotion): JsonResponse
    {
        $promotion->update(['is_active' => !$promotion->is_active]);

        return response()->json([
            'promotion_id' => $promotion->id,
            'is_active'    => $promotion->is_active,
        ]);
    }

    /**
     * GET /pos/orders/{order}/promotions
     * Obtiene promociones aplicables a un pedido (POS)
     */
    public function available(Request $request, Order $order): JsonResponse
    {
        $promotions = $this->promotionEngine->getAvailablePromotions($order);
        return response()->json(['data' => $promotions]);
    }

    /**
     * POST /pos/orders/{order}/promotions
     * Aplica una promoción a un pedido (POS)
     */
    public function applyToOrder(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'promotion_id' => 'required|integer|exists:promotions,id',
            'notes'        => 'nullable|string',
        ]);

        try {
            $orderPromotion = $this->promotionEngine->apply(
                $order,
                $validated['promotion_id'],
                $validated['notes'] ?? null
            );

            // Refrescar el order para devolver el new_total
            $order->refresh();

            return response()->json([
                'discount_applied' => $orderPromotion->discount_applied,
                'new_total'        => $order->total,
                'promotion_name'   => $orderPromotion->promotion->name ?? null,
                'free_item'        => $orderPromotion->free_item_variant_id ? [
                    'variant_id' => $orderPromotion->free_item_variant_id,
                    'quantity'   => $orderPromotion->free_item_quantity,
                ] : null,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * DELETE /pos/orders/{order}/promotions
     * Remueve la promoción de un pedido (POS)
     */
    public function removeFromOrder(Order $order): JsonResponse
    {
        try {
            $this->promotionEngine->remove($order);
            
            $order->refresh();

            return response()->json([
                'new_total' => $order->total,
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }
}
