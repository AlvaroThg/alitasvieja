<?php

namespace App\Modules\Menu\Services;

use App\Models\Branch;
use App\Modules\Menu\Models\Product;
use App\Modules\Menu\Models\ProductPrice;
use App\Modules\Menu\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PriceService
{
    /**
     * Retorna todos los productos activos con sus variants y precios por sucursal.
     * Estructura: agrupados por producto, con price_cbba y price_tja por variant.
     * Usa eager loading para evitar N+1.
     */
    public function getAllWithPrices(): Collection
    {
        // Cargar todos los branches activos
        $branches = Branch::active()->get();

        // Cargar todos los variants activos con su producto y precios
        $variants = ProductVariant::where('is_active', true)
            ->with(['product'])
            ->get();

        // Cargar TODOS los precios en una sola query
        $prices = ProductPrice::whereIn(
            'product_variant_id',
            $variants->pluck('id')
        )->get();

        // Indexar precios: [variant_id][branch_slug] => price
        $priceMap = [];
        foreach ($prices as $price) {
            $branch = $branches->firstWhere('id', $price->branch_id);
            if ($branch) {
                $priceMap[$price->product_variant_id][$branch->slug] = (float) $price->price;
            }
        }

        // Agrupar por producto
        return $variants->groupBy('product_id')->map(function ($productVariants) use ($priceMap) {
            $product = $productVariants->first()->product;

            return [
                'product' => [
                    'id'        => $product->id,
                    'name'      => $product->name,
                    'has_sauces' => $product->has_sauces ?? false,
                ],
                'variants' => $productVariants->map(function ($variant) use ($priceMap) {
                    return [
                        'id'          => $variant->id,
                        'name'        => $variant->name,
                        'wings_count' => $variant->wings_count ?? null,
                        'max_sauces'  => $variant->max_sauces ?? null,
                        'price_cbba'  => $priceMap[$variant->id]['cbba'] ?? null,
                        'price_tja'   => $priceMap[$variant->id]['tja'] ?? null,
                    ];
                })->values(),
            ];
        })->values();
    }

    /**
     * Actualiza o crea el precio de un variant para una sucursal.
     *
     * @throws ValidationException
     */
    public function updatePrice(int $productVariantId, int $branchId, float $price): ProductPrice
    {
        if ($price < 0) {
            throw ValidationException::withMessages([
                'price' => 'El precio no puede ser negativo.',
            ]);
        }

        $variant = ProductVariant::where('id', $productVariantId)
            ->where('is_active', true)
            ->first();

        if (!$variant) {
            throw ValidationException::withMessages([
                'product_variant_id' => 'El variant no existe o no está activo.',
            ]);
        }

        $branch = Branch::where('id', $branchId)
            ->where('is_active', true)
            ->first();

        if (!$branch) {
            throw ValidationException::withMessages([
                'branch_id' => 'La sucursal no existe o no está activa.',
            ]);
        }

        return ProductPrice::updateOrCreate(
            [
                'product_variant_id' => $productVariantId,
                'branch_id'          => $branchId,
            ],
            ['price' => $price]
        );
    }

    /**
     * Actualización masiva de precios para una sucursal.
     *
     * @param  int    $branchId
     * @param  array  $prices  [['product_variant_id' => int, 'price' => float], ...]
     * @return array  [{ variant_id, price, updated: bool }]
     *
     * @throws ValidationException
     */
    public function bulkUpdatePrices(int $branchId, array $prices): array
    {
        return DB::transaction(function () use ($branchId, $prices) {
            $results = [];

            foreach ($prices as $index => $entry) {
                try {
                    $record = $this->updatePrice(
                        $entry['product_variant_id'],
                        $branchId,
                        (float) $entry['price']
                    );

                    $results[] = [
                        'variant_id' => $entry['product_variant_id'],
                        'price'      => (float) $record->price,
                        'updated'    => true,
                    ];
                } catch (ValidationException $e) {
                    // Rollback se maneja automáticamente por DB::transaction
                    throw ValidationException::withMessages([
                        "prices.{$index}" => "Error al actualizar variant #{$entry['product_variant_id']}: "
                            . implode(', ', array_merge(...array_values($e->errors()))),
                    ]);
                }
            }

            return $results;
        });
    }
}
