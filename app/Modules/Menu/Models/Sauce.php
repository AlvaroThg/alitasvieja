<?php

namespace App\Modules\Menu\Models;

use Illuminate\Database\Eloquent\Model;

class Sauce extends Model
{
    protected $fillable = ['name', 'spice_level'];

    protected function casts(): array
    {
        return [
            'spice_level' => 'integer',
        ];
    }
}
