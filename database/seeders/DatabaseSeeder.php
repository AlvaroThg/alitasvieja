<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Table; // Importamos el modelo que creaste
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->call([\Database\Seeders\ProductionSeeder::class]);
        } else {
            $this->call([\Database\Seeders\PosStagingSeeder::class]);
        }
    }
}