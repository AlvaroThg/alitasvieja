<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Usuarios de prueba para testing exploratorio de roles y permisos.
 *
 * Idempotente: se puede re-ejecutar sin duplicar datos.
 *   php artisan db:seed --class=TestUsersSeeder
 *
 * Todos los usuarios usan la contraseña: password123
 */
class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Sucursales (reutiliza Cochabamba si ya existe, crea Tarija) ───
        $cbba = Branch::firstOrCreate(
            ['city' => 'Cochabamba'],
            ['name' => 'Sucursal Cochabamba', 'slug' => 'cbba', 'address' => 'Cochabamba', 'phone' => '00000000', 'is_active' => true]
        );

        $tja = Branch::firstOrCreate(
            ['city' => 'Tarija'],
            ['name' => 'Sucursal Tarija', 'slug' => 'tja', 'address' => 'Tarija', 'phone' => '00000000', 'is_active' => true]
        );

        // ─── Usuarios por rol ──────────────────────────────────────────────
        $users = [
            ['email' => 'owner@test.com',      'name' => 'Owner (Dueño)',        'role' => 'owner',        'branch_id' => $cbba->id],
            ['email' => 'admin.cbba@test.com', 'name' => 'Admin Cochabamba',     'role' => 'branch_admin', 'branch_id' => $cbba->id],
            ['email' => 'cajero.cbba@test.com','name' => 'Cajero Cochabamba',    'role' => 'cashier',      'branch_id' => $cbba->id],
            ['email' => 'cajero.tja@test.com', 'name' => 'Cajero Tarija',        'role' => 'cashier',      'branch_id' => $tja->id],
            ['email' => 'mozo.cbba@test.com',  'name' => 'Mozo Cochabamba',      'role' => 'waiter',       'branch_id' => $cbba->id],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name'      => $u['name'],
                    'role'      => $u['role'],
                    'branch_id' => $u['branch_id'],
                    'password'  => Hash::make('password123'),
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('Usuarios de prueba creados (password: password123).');
    }
}
