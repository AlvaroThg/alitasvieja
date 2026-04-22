<?php

namespace App\Modules\Cash\Models;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashSession extends Model
{
    protected $fillable = [
        'branch_id',
        'opened_by',
        'closed_by',
        'opening_amount',
        'closing_amount',
        'expected_amount',
        'difference',
        'notes',
        'opened_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'opening_amount'  => 'decimal:2',
            'closing_amount'  => 'decimal:2',
            'expected_amount' => 'decimal:2',
            'difference'      => 'decimal:2',
            'opened_at'       => 'datetime',
            'closed_at'       => 'datetime',
        ];
    }

    // ─── Relaciones ───────────────────────────────────────────

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(CashMovement::class);
    }

    // ─── Scopes ───────────────────────────────────────────────

    public function scopeOpen($query)
    {
        return $query->whereNull('closed_at');
    }

    public function scopeByBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    // ─── Accessors ────────────────────────────────────────────

    public function getIsOpenAttribute(): bool
    {
        return $this->closed_at === null;
    }

    public function getTotalIncomesAttribute(): float
    {
        return (float) $this->movements()->where('type', 'income')->sum('amount');
    }

    public function getTotalExpensesAttribute(): float
    {
        return (float) $this->movements()->where('type', 'expense')->sum('amount');
    }

    // ─── Métodos ──────────────────────────────────────────────

    /**
     * Calcula el monto esperado en caja.
     * expected = opening + ingresos - egresos
     */
    public function calculateExpected(): float
    {
        return (float) $this->opening_amount
            + $this->total_incomes
            - $this->total_expenses;
    }
}
