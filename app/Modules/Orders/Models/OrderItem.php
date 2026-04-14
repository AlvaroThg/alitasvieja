<?php

namespace App\Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_variant_id',
        'quantity',
        'unit_price',
        'extra_sauce_charge',
        'subtotal',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity'           => 'integer',
            'unit_price'         => 'decimal:2',
            'extra_sauce_charge' => 'decimal:2',
            'subtotal'           => 'decimal:2',
        ];
    }

    // ─── Relaciones ───────────────────────────────────────────

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relación con product_variants (migración de Marcelo).
     */
    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Menu\Models\ProductVariant::class, 'product_variant_id');
    }

    public function sauces(): HasMany
    {
        return $this->hasMany(OrderItemSauce::class);
    }
}
