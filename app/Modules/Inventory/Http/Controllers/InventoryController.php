<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class InventoryController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * GET /admin/inventory
     */
    public function index(Request $request): JsonResponse
    {
        // Se puede pasar branch_id, de lo contrario asume el activo si es branch_admin, 
        // o si es owner y no pasa branch, podría requerir un default.
        // Asumimos que usa activeBranchId() si no se pasa explícitamente.
        $branchId = $request->input('branch_id', $request->user()->activeBranchId());

        if (!$branchId) {
            return response()->json(['error' => 'Se requiere especificar una sucursal.'], 422);
        }

        $stock = $this->inventoryService->getStockByBranch((int) $branchId);

        return response()->json(['data' => $stock]);
    }

    /**
     * GET /admin/inventory/alerts
     */
    public function alerts(Request $request): JsonResponse
    {
        $branchId = $request->input('branch_id', $request->user()->activeBranchId());

        if (!$branchId) {
            return response()->json(['error' => 'Se requiere especificar una sucursal.'], 422);
        }

        $alerts = $this->inventoryService->getLowStockAlerts((int) $branchId);

        return response()->json(['data' => $alerts]);
    }

    /**
     * POST /admin/inventory/add
     */
    public function addStock(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_variant_id' => 'required|integer|exists:product_variants,id',
            'branch_id'          => 'required|integer|exists:branches,id',
            'quantity'           => 'required|integer|min:1',
            'reason'             => 'required|string|max:255',
        ]);

        try {
            $inventory = $this->inventoryService->addStock(
                $validated['product_variant_id'],
                $validated['branch_id'],
                $validated['quantity'],
                $request->user()->id,
                $validated['reason']
            );

            // Obtenemos el último movimiento
            $lastMovement = $inventory->movements()->latest('id')->first();

            return response()->json([
                'stock_quantity' => $inventory->stock_quantity,
                'movement_id'    => $lastMovement ? $lastMovement->id : null,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * PUT /admin/inventory/adjust
     */
    public function adjust(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_variant_id' => 'required|integer|exists:product_variants,id',
            'branch_id'          => 'required|integer|exists:branches,id',
            'new_quantity'       => 'required|integer|min:0',
            'reason'             => 'required|string|max:255',
        ]);

        try {
            // Buscamos el stock anterior antes de ajustar para retornarlo en la respuesta
            $stockBefore = \App\Modules\Inventory\Models\Inventory::where('product_variant_id', $validated['product_variant_id'])
                ->where('branch_id', $validated['branch_id'])
                ->value('stock_quantity') ?? 0;

            $inventory = $this->inventoryService->adjustStock(
                $validated['product_variant_id'],
                $validated['branch_id'],
                $validated['new_quantity'],
                $request->user()->id,
                $validated['reason']
            );

            $lastMovement = $inventory->movements()->latest('id')->first();

            return response()->json([
                'stock_quantity' => $inventory->stock_quantity,
                'stock_before'   => $stockBefore,
                'movement_id'    => $lastMovement ? $lastMovement->id : null,
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }
}
