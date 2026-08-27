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
        $branch = Branch::create(['name' => 'Sucursal Principal', 'slug' => 'sucursal-principal', 'address' => '...', 'city' => '...', 'phone' => '...', 'is_active' => true]);
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
            ->assertSee('Caja de Venta')
            ->assertSee('Cerrar Caja')
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

    public function test_cash_closing_requires_both_cash_and_qr()
    {
        $branch = Branch::create(['name' => 'Sucursal Principal', 'slug' => 'sucursal-principal', 'address' => '...', 'city' => '...', 'phone' => '...', 'is_active' => true]);
        $user = User::factory()->create([
            'role' => 'cashier',
            'branch_id' => $branch->id,
            'is_active' => true
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Cash\CashManager::class)
            ->set('opening_amount', 500)
            ->call('openSession')
            ->call('openCloseModal')
            ->call('closeSession')
            ->assertHasErrors(['closing_amount', 'closing_qr']);
    }

    public function test_cash_closing_with_cash_and_qr_success()
    {
        $branch = Branch::create(['name' => 'Sucursal Principal', 'slug' => 'sucursal-principal', 'address' => '...', 'city' => '...', 'phone' => '...', 'is_active' => true]);
        $user = User::factory()->create([
            'role' => 'cashier',
            'branch_id' => $branch->id,
            'is_active' => true
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Cash\CashManager::class)
            ->set('opening_amount', 500)
            ->call('openSession')
            ->call('openCloseModal')
            ->set('closing_amount', 500)
            ->set('closing_qr', 0)
            ->call('closeSession')
            ->assertHasNoErrors()
            ->assertSee('Apertura de Caja');
    }

    public function test_cash_closing_detects_surplus()
    {
        $branch = Branch::create(['name' => 'Sucursal Principal', 'slug' => 'sucursal-principal', 'address' => '...', 'city' => '...', 'phone' => '...', 'is_active' => true]);
        $user = User::factory()->create([
            'role' => 'cashier',
            'branch_id' => $branch->id,
            'is_active' => true
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Cash\CashManager::class)
            ->set('opening_amount', 500)
            ->call('openSession')
            ->call('openCloseModal')
            ->set('closing_amount', 600) // 100 surplus
            ->set('closing_qr', 0)
            ->call('closeSession')
            ->assertSet('showSurplusConfirm', true)
            ->assertSet('surplusAmount', 100)
            ->call('cancelSurplusConfirm')
            ->assertSet('showSurplusConfirm', false);
    }
}
