<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'branch_id',
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // ─── Relaciones ───────────────────────────────────────────

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // ─── Helpers de rol ───────────────────────────────────────

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isBranchAdmin(): bool
    {
        return $this->role === 'branch_admin';
    }

    public function isCashier(): bool
    {
        return $this->role === 'cashier';
    }

    public function isWaiter(): bool
    {
        return $this->role === 'waiter';
    }

    public function hasGlobalAccess(): bool
    {
        return $this->role === 'owner';
    }

    // Retorna el branch_id activo.
    // El owner puede "cambiar" de sucursal via sesión (ver AuthController).
    public function activeBranchId(): ?int
    {
        if ($this->isOwner()) {
            return session('active_branch_id');
        }
        return $this->branch_id;
    }
}
