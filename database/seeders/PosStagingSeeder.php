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
        // 0. Crear Sucursal Principal
        $branchId = DB::table('branches')->insertGetId([
            'name' => 'Sucursal Principal',
            'city' => 'Cochabamba',
            'slug' => 'cbba-' . uniqid(),
            'address' => 'Av. Siempre Viva 123',
            'phone' => '555-5555',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 1. Crear Usuario Administrador
        User::updateOrCreate(
            ['email' => 'admin@alitasvega.com'],
            [
                'name' => 'Admin Alitas',
                'password' => Hash::make('password123'),
                'branch_id' => $branchId,
                'role' => 'owner'
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
            DB::table('sauces')->updateOrInsert(['name' => $sauce['name']], $sauce);
        }

        // 3. Cargar Mesas (Tables) - adaptado al esquema real
        $tables = [
            ['name' => 'Mesa 1', 'status' => 'available', 'branch_id' => $branchId],
            ['name' => 'Mesa 2', 'status' => 'available', 'branch_id' => $branchId],
            ['name' => 'Mesa 3', 'status' => 'occupied', 'branch_id' => $branchId],
            ['name' => 'Mesa 4', 'status' => 'available', 'branch_id' => $branchId],
            ['name' => 'VIP 1', 'status' => 'reserved', 'branch_id' => $branchId],
        ];

        foreach ($tables as $table) {
            DB::table('tables')->updateOrInsert(['name' => $table['name']], $table);
        }

        // 4. Cargar Categorías, Productos y Variantes
        $catId = DB::table('categories')->insertGetId(['name' => 'Alitas', 'created_at' => now()]);
        
        $prodId = DB::table('products')->insertGetId([
            'category_id' => $catId,
            'name' => 'Alitas Clásicas',
            'is_wings' => true,
            'has_sauces' => true,
            'max_sauces' => 2,
            'created_at' => now()
        ]);

        DB::table('product_variants')->insert([
            ['product_id' => $prodId, 'name' => '5 Piezas', 'price' => 80.00, 'wings_count' => 5, 'max_sauces' => 2, 'created_at' => now()],
            ['product_id' => $prodId, 'name' => '10 Piezas', 'price' => 150.00, 'wings_count' => 10, 'max_sauces' => 3, 'created_at' => now()]
        ]);
        
        $this->command->info('¡Datos de Staging (Usuario, Salsas y Mesas) cargados correctamente!');
    }
}