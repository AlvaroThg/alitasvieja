<?php

namespace App\Modules\Inventory\Models;

use App\Models\Branch;
use App\Models\User;
use App\Modules\Menu\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    protected $fillable = [
        'product_variant_id',
        'branch_id',
        'user_id',
        'type',
        'quantity',
        'stock_before',
        'stock_after',
        'reason',
        'reference_id',
        'reference_type',
    ];

    protected function casts(): array
    {
        return [
            'quantity'     => 'integer',
            'stock_before' => 'integer',
            'stock_after'  => 'integer',
        ];
    }

    // ─── Relaciones ───────────────────────────────────────────

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
