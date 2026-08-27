<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchAdminNavigationTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;
    private User $branchAdmin;
    private User $cashier;
    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'Sucursal Central',
            'slug' => 'central',
            'address' => 'Av. 16 de Julio',
            'city' => 'La Paz',
            'phone' => '77712345',
            'is_active' => true,
        ]);

        $this->branchAdmin = User::factory()->create([
            'role' => 'branch_admin',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);

        $this->cashier = User::factory()->create([
            'role' => 'cashier',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);

        $this->owner = User::factory()->create([
            'role' => 'owner',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
    }

    public function test_branch_admin_can_access_pos()
    {
        $response = $this->actingAs($this->branchAdmin)->get(route('pos.index'));
        $response->assertStatus(200);
        $response->assertSee('Inventario');
        $response->assertSee('Caja');
    }

    public function test_branch_admin_inventory_back_button_points_to_pos()
    {
        $response = $this->actingAs($this->branchAdmin)->get(route('admin.inventory.index'));
        $response->assertStatus(200);
        $response->assertSee(route('pos.index'));
        $response->assertDontSee(route('admin.dashboard'));
    }

    public function test_branch_admin_cash_back_button_points_to_pos()
    {
        $response = $this->actingAs($this->branchAdmin)->get(route('cash.movements'));
        $response->assertStatus(200);
        $response->assertSee(route('pos.index'));
        $response->assertDontSee(route('admin.dashboard'));
    }

    public function test_branch_admin_cannot_access_owner_dashboard()
    {
        $response = $this->actingAs($this->branchAdmin)->get(route('admin.dashboard'));
        $response->assertStatus(403);
    }

    public function test_cashier_inventory_back_button_points_to_pos()
    {
        $response = $this->actingAs($this->cashier)->get(route('admin.inventory.index'));
        $response->assertStatus(200);
        $response->assertSee(route('pos.index'));
    }

    public function test_owner_inventory_back_button_points_to_dashboard()
    {
        $response = $this->actingAs($this->owner)->get(route('admin.inventory.index'));
        $response->assertStatus(200);
        $response->assertSee(route('admin.dashboard'));
    }

    public function test_owner_can_access_owner_dashboard()
    {
        $response = $this->actingAs($this->owner)->get(route('admin.dashboard'));
        $response->assertStatus(200);
    }
}
