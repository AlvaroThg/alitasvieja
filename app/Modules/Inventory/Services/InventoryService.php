<?php

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Models\Inventory;
use App\Modules\Inventory\Models\InventoryMovement;
use App\Modules\Menu\Models\ProductVariant;
use App\Modules\Orders\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    /**
     * Descuenta el inventario al cerrar un pedido.
     * Si no hay stock suficiente, permite que quede negativo y loggea un warning.
     * Ignora las variantes cuyo producto no rastrea stock.
     */
    public function decrementOnSale(Order $order): void
    {
        DB::transaction(function () use ($order) {
            // Iterar sobre los ítems del pedido (requiere eager loading de productVariant.product en el controlador/servicio que lo llame)
            foreach ($order->items as $item) {
                // Validación para evitar N+1 si no está cargado (fallback seguro)
                $productVariant = $item->productVariant()->with('product')->first();
                if (!$productVariant || !$productVariant->product) {
                    continue;
                }

                $product = $productVariant->product;

                // REGLA: las alitas usan su propio control de stock por kilos,
                // no este inventario. Todo lo demás (helados, bebidas, etc.) sí.
                if ($product->is_wings) {
                    continue;
                }

                $inventory = Inventory::where('product_variant_id', $item->product_variant_id)
                    ->where('branch_id', $order->branch_id)
                    ->lockForUpdate()
                    ->first();

                if (!$inventory) {
                    // El producto no está registrado en el inventario de esta sucursal:
                    // no se descuenta (no todos los productos llevan inventario).
                    continue;
                }

                $stockBefore = $inventory->stock_quantity;
                $newQuantity = $stockBefore - $item->quantity;

                if ($newQuantity < 0) {
                    Log::warning("Stock negativo en variant {$item->product_variant_id} en branch {$order->branch_id} después de la venta #{$order->order_number}. Stock actual: {$newQuantity}.");
                }

                $inventory->update([
                    'stock_quantity' => $newQuantity,
                ]);

                InventoryMovement::create([
                    'product_variant_id' => $item->product_variant_id,
                    'branch_id'          => $order->branch_id,
                    'user_id'            => null, // Automático por sistema
                    'type'               => 'sale',
                    'quantity'           => $item->quantity,
                    'stock_before'       => $stockBefore,
                    'stock_after'        => $newQuantity,
                    'reason'             => "Venta de pedido {$order->order_number}",
                    'reference_id'       => $item->id,
                    'reference_type'     => 'order_item',
                ]);
            }
        });
    }

    /**
     * Ajuste manual de inventario.
     *
     * @throws ValidationException
     */
    public function adjustStock(int $productVariantId, int $branchId, int $newQuantity, int $userId, string $reason): Inventory
    {
        return DB::transaction(function () use ($productVariantId, $branchId, $newQuantity, $userId, $reason) {
            $variant = ProductVariant::with('product')->find($productVariantId);

            if (!$variant || $variant->product->is_wings) {
                throw ValidationException::withMessages([
                    'product_variant_id' => 'El producto no existe o es de alitas (usa control de stock por kilos).',
                ]);
            }

            if ($newQuantity < 0) {
                throw ValidationException::withMessages([
                    'new_quantity' => 'La nueva cantidad no puede ser negativa en un ajuste manual.',
                ]);
            }

            $inventory = Inventory::firstOrCreate(
                ['product_variant_id' => $productVariantId, 'branch_id' => $branchId],
                ['stock_quantity' => 0, 'minimum_alert' => 0]
            );

            $stockBefore = $inventory->stock_quantity;
            $quantityDiff = abs($newQuantity - $stockBefore);

            if ($quantityDiff === 0) {
                return $inventory; // No hay cambio
            }

            $inventory->update(['stock_quantity' => $newQuantity]);

            InventoryMovement::create([
                'product_variant_id' => $productVariantId,
                'branch_id'          => $branchId,
                'user_id'            => $userId,
                'type'               => 'adjustment',
                'quantity'           => $quantityDiff,
                'stock_before'       => $stockBefore,
                'stock_after'        => $newQuantity,
                'reason'             => $reason,
            ]);

            return $inventory;
        });
    }

    /**
     * Entrada de stock (compra/recepción).
     *
     * @throws ValidationException
     */
    public function addStock(int $productVariantId, int $branchId, int $quantity, int $userId, string $reason): Inventory
    {
        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => 'La cantidad a ingresar debe ser mayor a 0.',
            ]);
        }

        return DB::transaction(function () use ($productVariantId, $branchId, $quantity, $userId, $reason) {
            $variant = ProductVariant::with('product')->find($productVariantId);

            if (!$variant || $variant->product->is_wings) {
                throw ValidationException::withMessages([
                    'product_variant_id' => 'El producto no existe o es de alitas (usa control de stock por kilos).',
                ]);
            }

            $inventory = Inventory::firstOrCreate(
                ['product_variant_id' => $productVariantId, 'branch_id' => $branchId],
                ['stock_quantity' => 0, 'minimum_alert' => 0]
            );

            $stockBefore = $inventory->stock_quantity;
            $newQuantity = $stockBefore + $quantity;

            $inventory->update(['stock_quantity' => $newQuantity]);

            InventoryMovement::create([
                'product_variant_id' => $productVariantId,
                'branch_id'          => $branchId,
                'user_id'            => $userId,
                'type'               => 'in',
                'quantity'           => $quantity,
                'stock_before'       => $stockBefore,
                'stock_after'        => $newQuantity,
                'reason'             => $reason,
            ]);

            return $inventory;
        });
    }

    /**
     * Obtiene el stock de una sucursal con sus últimos movimientos.
     */
    public function getStockByBranch(int $branchId): Collection
    {
        return Inventory::byBranch($branchId)
            ->with(['productVariant.product', 'movements' => function ($query) {
                $query->orderByDesc('id')->limit(5);
            }])
            ->get();
    }

    /**
     * Obtiene alertas de stock bajo para una sucursal.
     */
    public function getLowStockAlerts(int $branchId): Collection
    {
        return Inventory::byBranch($branchId)
            ->belowAlert()
            ->with('productVariant.product')
            ->get();
    }
}
