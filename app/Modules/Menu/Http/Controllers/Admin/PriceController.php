<?php

namespace App\Modules\Menu\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Menu\Models\ProductVariant;
use App\Modules\Menu\Services\PriceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PriceController extends Controller
{
    protected PriceService $priceService;

    public function __construct(PriceService $priceService)
    {
        $this->priceService = $priceService;
    }

    /**
     * GET /admin/prices
     */
    public function index(): JsonResponse
    {
        $data = $this->priceService->getAllWithPrices();

        return response()->json(['data' => $data]);
    }

    /**
     * PUT /admin/prices/{variant}/branch/{branch}
     */
    public function update(Request $request, int $variant, int $branch): JsonResponse
    {
        $validated = $request->validate([
            'price' => 'required|numeric|min:0',
        ]);

        try {
            $record = $this->priceService->updatePrice(
                $variant,
                $branch,
                (float) $validated['price']
            );

            return response()->json([
                'variant_id'  => $record->product_variant_id,
                'branch_id'   => $record->branch_id,
                'price'       => $record->price,
                'updated_at'  => $record->updated_at,
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * PUT /admin/prices/bulk
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id'                    => 'required|integer|exists:branches,id',
            'prices'                       => 'required|array|min:1',
            'prices.*.product_variant_id'  => 'required|integer|exists:product_variants,id',
            'prices.*.price'               => 'required|numeric|min:0',
        ]);

        try {
            $results = $this->priceService->bulkUpdatePrices(
                $validated['branch_id'],
                $validated['prices']
            );

            return response()->json([
                'updated' => count($results),
                'results' => $results,
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }
}
