<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear Sucursal CBB (Cochabamba) y TJA (Tarija)
        $cbbaId = DB::table('branches')->insertGetId([
            'name' => 'Sucursal Cochabamba',
            'city' => 'Cochabamba',
            'slug' => 'cbba',
            'address' => 'Dirección CBB',
            'phone' => '00000000',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $tjaId = DB::table('branches')->insertGetId([
            'name' => 'Sucursal Tarija',
            'city' => 'Tarija',
            'slug' => 'tja',
            'address' => 'Dirección TJA',
            'phone' => '00000000',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 2. Crear Usuario Administrador (Owner)
        User::updateOrCreate(
            ['email' => 'admin@alitasvega.com'],
            [
                'name' => 'Admin Alitas',
                'password' => Hash::make('password123'), // Forzar cambio en primer inicio
                'branch_id' => $cbbaId,
                'role' => 'owner'
            ]
        );

        // 3. Cargar Salsas Base (Sauces)
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
            DB::table('sauces')->updateOrInsert(['name' => $sauce['name']], $sauce);
        }

        // 3.5. Crear Categoría Inicial "Bebidas"
        DB::table('categories')->updateOrInsert(
            ['name' => 'Bebidas'],
            ['is_active' => true, 'created_at' => now(), 'updated_at' => now()]
        );

        // 4. Cargar Mesas (Tables) iniciales
        for ($i = 1; $i <= 10; $i++) {
            DB::table('tables')->updateOrInsert(
                ['name' => "Mesa $i", 'branch_id' => $cbbaId],
                ['status' => 'available', 'created_at' => now(), 'updated_at' => now()]
            );
        }

        $this->command->info('¡Datos de Producción (Branches, Admin, Salsas y Mesas) cargados correctamente!');
    }
}
