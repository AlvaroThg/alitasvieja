<?php

namespace App\Modules\Promotions\Models;

use App\Modules\Menu\Models\ProductVariant;
use App\Modules\Orders\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPromotion extends Model
{
    protected $fillable = [
        'order_id',
        'promotion_id',
        'discount_applied',
        'free_item_variant_id',
        'free_item_quantity',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'discount_applied'   => 'decimal:2',
            'free_item_quantity' => 'integer',
        ];
    }

    // ─── Relaciones ───────────────────────────────────────────

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function freeItemVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'free_item_variant_id');
    }
}
