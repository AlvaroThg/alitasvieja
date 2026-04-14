<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class PosStagingSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear Usuario Administrador
        User::updateOrCreate(
            ['email' => 'admin@alitasvega.com'],
            [
                'name' => 'Admin Alitas',
                'password' => Hash::make('password123'),
            ]
        );

        // 2. Cargar Salsas (Sauces)
        $sauces = [
            ['name' => 'BBQ Clásica', 'spice_level' => 1],
            ['name' => 'Teriyaki', 'spice_level' => 1],
            ['name' => 'Ajo Parmesano', 'spice_level' => 1],
            ['name' => 'Buffalo Mild', 'spice_level' => 2],
            ['name' => 'Buffalo Hot', 'spice_level' => 4],
            ['name' => 'Mango Habanero', 'spice_level' => 5],
            ['name' => 'La Vieja (Extrema)', 'spice_level' => 10],
        ];

        foreach ($sauces as $sauce) {
            // CORRECCIÓN: Usamos updateOrInsert
            DB::table('sauces')->updateOrInsert(['name' => $sauce['name']], $sauce);
        }

        // 3. Cargar Mesas (Tables)
        $tables = [
            ['number' => 1, 'capacity' => 2, 'status' => 'available'],
            ['number' => 2, 'capacity' => 2, 'status' => 'available'],
            ['number' => 3, 'capacity' => 4, 'status' => 'available'],
            ['number' => 4, 'capacity' => 4, 'status' => 'available'],
            ['number' => 5, 'capacity' => 6, 'status' => 'available'],
            ['number' => 6, 'capacity' => 8, 'status' => 'available'],
        ];

        foreach ($tables as $table) {
            // CORRECCIÓN: Usamos updateOrInsert
            DB::table('tables')->updateOrInsert(['number' => $table['number']], $table);
        }
        
        $this->command->info('¡Datos de Staging (Usuario, Salsas y Mesas) cargados correctamente!');
    }
}