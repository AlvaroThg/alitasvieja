<?php

namespace App\Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemSauce extends Model
{
    protected $fillable = [
        'order_item_id',
        'sauce_id',
        'quantity',
        'is_coated',
    ];

    protected function casts(): array
    {
        return [
            'quantity'   => 'integer',
            'is_coated'  => 'boolean',
        ];
    }

    // ─── Relaciones ───────────────────────────────────────────

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Relación con sauces (migración de Marcelo).
     */
    public function sauce(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Menu\Models\Sauce::class, 'sauce_id');
    }
}
