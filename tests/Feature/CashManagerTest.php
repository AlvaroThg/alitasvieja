<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CashManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_cash_manager_without_active_branch()
    {
        $user = User::factory()->create(['role' => 'owner']);
        
        Livewire::actingAs($user)
            ->test(\App\Livewire\Cash\CashManager::class)
            ->assertSee('Apertura de Caja')
            ->set('opening_amount', 1500)
            ->call('openSession')
            ->assertHasErrors(['opening_amount']);
    }

    public function test_opens_session_with_active_branch()
    {
        $branch = Branch::create(['name' => 'Sucursal Principal', 'address' => '...', 'city' => '...', 'phone' => '...', 'is_active' => true]);
        $user = User::factory()->create([
            'role' => 'cashier',
            'branch_id' => $branch->id,
            'is_active' => true
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Cash\CashManager::class)
            ->assertSee('Apertura de Caja')
            ->set('opening_amount', 1500)
            ->call('openSession')
            ->assertHasNoErrors()
            ->assertSee('Caja Activa')
            ->assertSee('Nuevo Movimiento');
    }

    public function test_renders_product_manager()
    {
        $user = User::factory()->create(['role' => 'owner']);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Admin\ProductManager::class)
            ->assertSee('Gestión de Productos')
            ->assertSee('+ Nuevo Producto');
    }
}
