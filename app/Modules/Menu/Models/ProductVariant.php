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
}
