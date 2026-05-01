<?php

namespace App\Modules\Inventory\Models;

use App\Models\Branch;
use App\Modules\Menu\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventory extends Model
{
    protected $table = 'inventory';

    protected $fillable = [
        'product_variant_id',
        'branch_id',
        'stock_quantity',
        'minimum_alert',
    ];

    protected function casts(): array
    {
        return [
            'stock_quantity' => 'integer',
            'minimum_alert'  => 'integer',
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

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    // ─── Scopes ───────────────────────────────────────────────

    public function scopeByBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeBelowAlert($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'minimum_alert');
    }

    // ─── Métodos ──────────────────────────────────────────────

    public function isLow(): bool
    {
        return $this->stock_quantity <= $this->minimum_alert;
    }

    public function hasSufficientStock(int $quantity): bool
    {
        return $this->stock_quantity >= $quantity;
    }
}
