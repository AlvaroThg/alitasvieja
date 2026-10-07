<?php

namespace App\Modules\Menu\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'wings_count',
        'max_sauces',
        'price',
        'coated_price',
        'is_active'
    ];

    protected function casts(): array
    {
        return [
            'wings_count' => 'integer',
            'max_sauces' => 'integer',
            'price' => 'decimal:2',
            'coated_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Precios diferenciados por sucursal (tabla product_prices).
     */
    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    /**
     * Retorna el precio efectivo para la variante en una sucursal específica.
     * Si no existe un precio específico, devuelve el precio base.
     */
    public function priceForBranch(int $branchId): float
    {
        $branchPrice = \App\Modules\Menu\Models\ProductPrice::where('product_variant_id', $this->id)
            ->where('branch_id', $branchId)
            ->value('price');

        return $branchPrice !== null ? (float) $branchPrice : (float) $this->price;
    }

    /**
     * Retorna el precio extra efectivo de alitas/picadas bañadas para la variante en una sucursal específica.
     * 1. Recargo de la variante específico por sucursal (tabla product_prices)
     * 2. Recargo base de la variante (product_variants.coated_price)
     * 3. Recargo general de la sucursal (branches.sauce_coated_price)
     * 4. Fallback legacy para Cochabamba (5.00 Bs)
     */
    public function coatedPriceForBranch(int $branchId): float
    {
        $product = $this->product ?? null;
        if ($product && !$product->charge_coated_sauces) {
            return 0.0;
        }

        if ($this->id) {
            $branchRecord = \App\Modules\Menu\Models\ProductPrice::where('product_variant_id', $this->id)
                ->where('branch_id', $branchId)
                ->first();

            if ($branchRecord && $branchRecord->coated_price !== null) {
                return (float) $branchRecord->coated_price;
            }
        }

        if ($this->coated_price !== null && (float) $this->coated_price > 0.0) {
            return (float) $this->coated_price;
        }

        if ($this->coated_price !== null && (float) $this->coated_price === 0.0 && $this->exists) {
            return 0.0;
        }

        $branch = \App\Models\Branch::find($branchId);
        if ($branch && $branch->sauce_coated_price !== null && (float) $branch->sauce_coated_price > 0.0) {
            return (float) $branch->sauce_coated_price;
        }

        $city = strtolower($branch->city ?? '');
        $slug = strtolower($branch->slug ?? '');
        $name = strtolower($branch->name ?? '');
        if ($slug === 'cbba' || str_contains($slug, 'cbba') || str_contains($city, 'cochabamba') || str_contains($name, 'cochabamba')) {
            return 5.00;
        }

        return 0.0;
    }
}
