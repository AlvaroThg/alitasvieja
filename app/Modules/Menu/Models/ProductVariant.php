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
        'is_active'
    ];

    protected function casts(): array
    {
        return [
            'wings_count' => 'integer',
            'max_sauces' => 'integer',
            'price' => 'decimal:2',
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
}
