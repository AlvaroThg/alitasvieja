<?php

namespace App\Modules\Orders\Models;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    protected $fillable = [
        'branch_id',
        'table_id',
        'user_id',
        'order_number',
        'order_type',
        'daily_number',     // MODIFICADO: correlativo diario (OBS 1)
        'status',
        'subtotal',
        'discount',
        'total',
        'notes',
        'payment_method',
        'opened_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'daily_number'   => 'integer',  // MODIFICADO (OBS 1)
            'subtotal'       => 'decimal:2',
            'discount'       => 'decimal:2',
            'total'          => 'decimal:2',
            'opened_at'      => 'datetime',
            'closed_at'      => 'datetime',
        ];
    }

    // ─── Relaciones ───────────────────────────────────────────

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Relación con la tabla "tables" (mesas).
     * Se usa el modelo genérico porque la migración la genera Marcelo.
     */
    public function table(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Table::class, 'table_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(OrderPayment::class);
    }

    public function appliedPromotion()
    {
        return $this->hasOne(\App\Modules\Promotions\Models\OrderPromotion::class);
    }

    // ─── Scopes ───────────────────────────────────────────────

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeByBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    // MODIFICADO: nuevo scope (OBS 1)
    /**
     * Filtra pedidos abiertos hoy según opened_at.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('opened_at', today());
    }
    // FIN MODIFICADO

    // ─── Accessors ────────────────────────────────────────────

    /**
     * Solo se puede editar un pedido si su estado es 'open'.
     */
    public function getIsEditableAttribute(): bool
    {
        return $this->status === 'open';
    }

    // MODIFICADO: nuevo accessor (OBS 1)
    /**
     * Etiqueta legible para cocina/POS: "Pedido #3".
     */
    public function getDailyLabelAttribute(): string
    {
        return "Pedido #{$this->daily_number}";
    }
    // FIN MODIFICADO

    // ─── Métodos estáticos ────────────────────────────────────

    // MODIFICADO: retorna array con order_number + daily_number (OBS 1)
    /**
     * Genera el número de pedido histórico y el correlativo diario.
     *
     * @return array{order_number: string, daily_number: int}
     */
    public static function generateOrderNumber(int $branchId): array
    {
        return DB::transaction(function () use ($branchId) {
            $branch = Branch::findOrFail($branchId);

            // Prefijo: slug en mayúsculas, cortado a 3 caracteres
            // cbba → CBB, tja → TJA
            $prefix = strtoupper(substr($branch->slug, 0, 3));

            // ── Correlativo histórico (nunca se reinicia) ──
            $lastOrder = static::where('branch_id', $branchId)
                ->lockForUpdate()
                ->orderByDesc('id')
                ->first();

            if ($lastOrder) {
                $lastNumber = (int) substr($lastOrder->order_number, strlen($prefix) + 1);
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            $orderNumber = $prefix . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            // ── Correlativo diario (reinicia cada día) ──
            $todayCount = static::where('branch_id', $branchId)
                ->whereDate('opened_at', today())
                ->lockForUpdate()
                ->count();

            $dailyNumber = $todayCount + 1;

            return [
                'order_number' => $orderNumber,
                'daily_number' => $dailyNumber,
            ];
        });
    }
    // FIN MODIFICADO
}
