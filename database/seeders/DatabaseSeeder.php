<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Table; // Importamos el modelo que creaste
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([\Database\Seeders\PosStagingSeeder::class]);
    }
}