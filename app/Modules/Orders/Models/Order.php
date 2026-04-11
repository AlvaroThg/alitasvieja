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
            'subtotal'    => 'decimal:2',
            'discount'    => 'decimal:2',
            'total'       => 'decimal:2',
            'opened_at'   => 'datetime',
            'closed_at'   => 'datetime',
        ];
    }

    // ─── Relaciones ───────────────────────────────────────────

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Relación con la mesa. Se usa el FQCN como string para evitar
     * dependencia circular; la migración de tables la genera Marcelo.
     */
    public function table(): BelongsTo
    {
        return $this->belongsTo('App\Modules\Tables\Models\Table');
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
     * El pedido solo es editable mientras esté abierto.
     */
    public function getIsEditableAttribute(): bool
    {
        return $this->status === 'open';
    }

    // ─── Generador de número de orden ─────────────────────────

    /**
     * Genera un número de orden secuencial por sucursal.
     * Formato: CBB-0001, TJA-0001, etc.
     * Usa lockForUpdate() dentro de una transacción para evitar colisiones.
     *
     * NOTA: El slug de branch se mapea a un prefijo de 3 letras.
     *   cbba → CBB | tja → TJA
     */
    public static function generateOrderNumber(int $branchId): string
    {
        return DB::transaction(function () use ($branchId) {
            $branch = Branch::findOrFail($branchId);
            $slug   = $branch->slug; // "cbba" o "tja"

            // Mapeo de slug a prefijo de 3 caracteres para el correlativo
            $prefixMap = [
                'cbba' => 'CBB',
                'tja'  => 'TJA',
            ];
            $prefix = $prefixMap[$slug] ?? strtoupper(substr($slug, 0, 3));

            // Obtener el último pedido de esta sucursal con lock
            $lastOrder = static::where('branch_id', $branchId)
                ->lockForUpdate()
                ->orderByDesc('id')
                ->first();

            $nextNumber = 1;
            if ($lastOrder) {
                // Extraer la parte numérica del order_number: "CBB-0042" → 42
                $parts = explode('-', $lastOrder->order_number);
                $nextNumber = ((int) end($parts)) + 1;
            }

            return $prefix . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        });
    }
}
