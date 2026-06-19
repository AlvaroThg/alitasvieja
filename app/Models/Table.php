<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    use HasFactory;

    
    protected $fillable = ['name', 'status', 'branch_id'];

    public function orders()
    {
        return $this->hasMany(\App\Modules\Orders\Models\Order::class, 'table_id');
    }

    public function hasActiveOrders(): bool
    {
        return $this->orders()->whereIn('status', ['open', 'pending'])->exists();
    }
}