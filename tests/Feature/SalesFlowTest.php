<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Branch;
use App\Modules\Orders\Services\CheckoutService;
use App\Modules\Orders\Services\OrderService;
use App\Modules\Orders\Services\WingSauceValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SalesFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_sales_flow_with_sauces()
    {
        // 1. Setup inicial
        $branch = Branch::create([
            'name' => 'Sucursal Central',
            'slug' => 'central',
            'address' => '...',
            'city' => '...',
            'phone' => '...',
            'is_active' => true
        ]);
        
        $user = User::factory()->create([
            'role' => 'cashier',
            'branch_id' => $branch->id,
            'is_active' => true
        ]);

        $this->actingAs($user);

        // Abrir caja para la sucursal
        $cashService = app(\App\Modules\Cash\Services\CashService::class);
        $cashSession = $cashService->openSession($branch->id, $user->id, 1000);

        // 2. Simular un producto (Alitas) y validación de salsas
        $validator = app(WingSauceValidator::class);
        
        $variant = new \stdClass();
        $variant->wings_count = 6;
        $variant->max_sauces = 2;

        $saucesData = [
            [
                'sauce_id' => 1,
                'quantity' => 4,
                'is_coated' => true,
            ],
            [
                'sauce_id' => 2,
                'quantity' => 2,
                'is_coated' => false,
            ]
        ];

        // Regla: la suma total es 6 (4 + 2). Esto no debe arrojar excepción de validación.
        $extraCharge = $validator->validate($variant, $branch->id, $saucesData);
        
        $this->assertEquals(0, $extraCharge, 'No debería cobrar extra por salsas en la sucursal por defecto');

        // 3. Crear orden
        $orderService = app(OrderService::class);
        $order = $orderService->createOrder(
            $branch->id,
            null, // tableId = null (para llevar)
            $user->id,
            'Orden de prueba',
            'takeaway'
        );

        // La orden debe estar creada con estado open
        $this->assertEquals('open', $order->status);

        // 4. Cobrar orden
        $checkoutService = app(CheckoutService::class);
        
        // Simular que la orden tiene total 50 en la BD
        $order->update(['total' => 50.00]);

        $payments = [
            ['method' => 'cash', 'amount' => 50.00]
        ];

        $checkoutService->processPayment($order, $payments);

        // La orden ahora debe estar pagada
        $order->refresh();
        $this->assertEquals('paid', $order->status);

        // Verificar que el movimiento de caja se registró
        $movements = $cashSession->movements()->get();
        $this->assertGreaterThanOrEqual(1, $movements->count());
        
        $paymentMovement = $movements->last();
        $this->assertEquals(50.00, $paymentMovement->amount);
    }

    public function test_unpaid_order_total_recalculation_on_load()
    {
        $branch = Branch::create([
            'name' => 'Sucursal Central',
            'slug' => 'central',
            'address' => '...',
            'city' => '...',
            'phone' => '...',
            'is_active' => true
        ]);
        
        $user = User::factory()->create([
            'role' => 'cashier',
            'branch_id' => $branch->id,
            'is_active' => true
        ]);

        $this->actingAs($user);

        // Crear una categoría y producto con precio
        $cat = \App\Modules\Menu\Models\Category::create(['name' => 'Alitas', 'is_active' => true]);
        $prod = \App\Modules\Menu\Models\Product::create(['category_id' => $cat->id, 'name' => 'Alitas 6 Pzs', 'is_active' => true]);
        $variant = \App\Modules\Menu\Models\ProductVariant::create([
            'product_id' => $prod->id,
            'name' => 'Original',
            'price' => 35.00,
            'is_active' => true
        ]);

        $orderService = app(OrderService::class);
        $order = $orderService->createOrder($branch->id, null, $user->id, 'Para llevar', 'takeaway');
        $orderService->addItem($order, [
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        // Simular orden e ítems antiguos desfasados con total y subtotal en 0 en la BD
        $order->update(['total' => 0.00, 'subtotal' => 0.00]);
        $order->items()->update(['unit_price' => 0.00, 'subtotal' => 0.00]);
        $this->assertEquals(0.00, (float) $order->fresh()->total);

        // Al ejecutar OrderBuilder@loadUnpaidOrders, debe auto-recalcular el total a 70.00
        \Livewire\Livewire::test(\App\Livewire\Pos\OrderBuilder::class)
            ->call('loadUnpaidOrders');

        $this->assertEquals(70.00, (float) $order->fresh()->total);
    }

    public function test_table_edit_and_safe_delete()
    {
        $branch = Branch::create([
            'name' => 'Sucursal Central',
            'slug' => 'central',
            'address' => '...',
            'city' => '...',
            'phone' => '...',
            'is_active' => true
        ]);
        
        $user = User::factory()->create([
            'role' => 'cashier',
            'branch_id' => $branch->id,
            'is_active' => true
        ]);

        $this->actingAs($user);

        $table = \App\Models\Table::create([
            'name' => 'Mesa 1',
            'status' => 'available',
            'branch_id' => $branch->id,
        ]);

        // 1. Probar edición de nombre de mesa
        \Livewire\Livewire::test(\App\Livewire\Pos\TableGrid::class)
            ->call('openEditTableModal', $table->id)
            ->set('editingTableName', 'Mesa VIP 1')
            ->call('updateTable');

        $this->assertEquals('Mesa VIP 1', $table->fresh()->name);

        // 2. Asociar un pedido anterior pagado a la mesa
        $orderService = app(OrderService::class);
        $order = $orderService->createOrder($branch->id, $table->id, $user->id, null, 'dine_in');
        $order->update(['status' => 'paid']);

        // 3. Probar eliminación de mesa con pedido asignado
        \Livewire\Livewire::test(\App\Livewire\Pos\TableGrid::class)
            ->call('confirmDeleteTable', $table->id)
            ->call('deleteTable');

        $this->assertNull(\App\Models\Table::find($table->id));
        $this->assertNull($order->fresh()->table_id);
    }
}
