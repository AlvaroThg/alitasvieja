<?php

namespace App\Modules\Promotions\Models;

use App\Models\Branch;
use App\Modules\Menu\Models\ProductVariant;
use App\Modules\Orders\Models\Order;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Promotion extends Model
{
    protected $fillable = [
        'branch_id',
        'name',
        'description',
        'type',
        'discount_type',
        'discount_value',
        'free_product_variant_id',
        'free_quantity',
        'conditions',
        'starts_at',
        'ends_at',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'free_quantity'  => 'integer',
            'conditions'     => 'array',
            'starts_at'      => 'date',
            'ends_at'        => 'date',
            'is_active'      => 'boolean',
        ];
    }

    // ─── Relaciones ───────────────────────────────────────────

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function freeProductVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'free_product_variant_id');
    }

    public function orderPromotions(): HasMany
    {
        return $this->hasMany(OrderPromotion::class);
    }

    // ─── Scopes ───────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $today = Carbon::today()->toDateString();
                $q->where(function ($sub) use ($today) {
                    $sub->whereNull('starts_at')
                        ->orWhere('starts_at', '<=', $today);
                })->where(function ($sub) use ($today) {
                    $sub->whereNull('ends_at')
                        ->orWhere('ends_at', '>=', $today);
                });
            });
    }

    public function scopeForBranch($query, int $branchId)
    {
        return $query->where(function ($q) use ($branchId) {
            $q->whereNull('branch_id')
              ->orWhere('branch_id', $branchId);
        });
    }

    // ─── Métodos ──────────────────────────────────────────────

    /**
     * Evalúa el JSON `conditions` para determinar si aplica al pedido.
     */
    public function isApplicable(Order $order): bool
    {
        if (empty($this->conditions) || !is_array($this->conditions)) {
            return true;
        }

        // Condición: min_order_total
        if (isset($this->conditions['min_order_total'])) {
            $minTotal = (float) $this->conditions['min_order_total'];
            // Se usa el subtotal ANTES de descuentos.
            if ((float) $order->subtotal < $minTotal) {
                return false;
            }
        }

        // Condición: applicable_variants
        if (isset($this->conditions['applicable_variants']) && is_array($this->conditions['applicable_variants'])) {
            $applicableIds = $this->conditions['applicable_variants'];
            
            // Verifica si el pedido contiene AL MENOS UNA de las variantes requeridas
            $hasVariant = false;
            foreach ($order->items as $item) {
                if (in_array($item->product_variant_id, $applicableIds)) {
                    $hasVariant = true;
                    break;
                }
            }
            if (!$hasVariant) {
                return false;
            }
        }

        // "requires_birthday": el cajero lo verifica, así que el motor asume true.
        // Otras condiciones se pueden extender aquí.

        return true;
    }
}
