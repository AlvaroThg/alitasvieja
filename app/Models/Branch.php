<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'city',
        'slug',
        'address',
        'phone',
        'is_active',
        'petty_cash_balance',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'petty_cash_balance' => 'decimal:2',
        ];
    }

    // ─── Relaciones ───────────────────────────────────────────

    public function users()
    {
        return $this->hasMany(User::class);
    }

    // ─── Scopes ───────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
