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
        'sauce_coated_price',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'petty_cash_balance' => 'decimal:2',
            'sauce_coated_price' => 'decimal:2',
        ];
    }

    protected static function booted()
    {
        static::creating(function ($branch) {
            if (!isset($branch->sauce_coated_price)) {
                $slug = strtolower($branch->slug ?? '');
                $city = strtolower($branch->city ?? '');
                $name = strtolower($branch->name ?? '');
                if ($slug === 'cbba' || str_contains($slug, 'cbba') || str_contains($city, 'cochabamba') || str_contains($name, 'cochabamba')) {
                    $branch->sauce_coated_price = 5.00;
                } else {
                    $branch->sauce_coated_price = 0.00;
                }
            }
        });
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
