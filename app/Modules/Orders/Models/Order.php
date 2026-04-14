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
        return $this->belongsTo(\App\Modules\Tables\Models\Table::class, 'table_id');
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

    // ─── Accessors ────────────────────────────────────────────

    /**
     * Solo se puede editar un pedido si su estado es 'open'.
     */
    public function getIsEditableAttribute(): bool
    {
        return $this->status === 'open';
    }

    // ─── Métodos estáticos ────────────────────────────────────

    /**
     * Genera un número de pedido correlativo por sucursal.
     * Formato: CBB-0001, TJA-0001, etc.
     * Usa lockForUpdate() dentro de transacción para evitar colisiones.
     */
    public static function generateOrderNumber(int $branchId): string
    {
        return DB::transaction(function () use ($branchId) {
            $branch = Branch::findOrFail($branchId);

            // Prefijo: slug en mayúsculas, cortado a 3 caracteres
            // cbba → CBB, tja → TJA
            $prefix = strtoupper(substr($branch->slug, 0, 3));

            // Obtener el último pedido de esta sucursal con lock
            $lastOrder = static::where('branch_id', $branchId)
                ->lockForUpdate()
                ->orderByDesc('id')
                ->first();

            if ($lastOrder) {
                // Extraer el número del último order_number (ej: "CBB-0042" → 42)
                $lastNumber = (int) substr($lastOrder->order_number, strlen($prefix) + 1);
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            return $prefix . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        });
    }
}
