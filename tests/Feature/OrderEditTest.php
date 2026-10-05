<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Table;
use App\Models\User;
use App\Modules\Menu\Models\Category;
use App\Modules\Menu\Models\Product;
use App\Modules\Menu\Models\ProductVariant;
use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Services\OrderService;
use App\Livewire\Pos\OrderBuilder;
use App\Livewire\Pos\TableGrid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrderEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_load_order_for_editing_and_update_it()
    {
        $branch = Branch::create([
            'name' => 'Sucursal Central',
            'slug' => 'central',
            'address' => 'Av. Principal 123',
            'city' => 'Santa Cruz',
            'phone' => '70000000',
            'is_active' => true
        ]);

        $user = User::factory()->create([
            'role' => 'cashier',
            'branch_id' => $branch->id,
            'is_active' => true
        ]);

        $table = Table::create([
            'branch_id' => $branch->id,
            'name' => 'Mesa 1',
            'status' => 'occupied',
        ]);

        $category = Category::create([
            'name' => 'Alitas',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Alitas Tradicionales',
            'has_sauces' => false,
            'is_active' => true,
        ]);

        $variant1 = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Porción 6 uds',
            'price' => 30.00,
            'wings_count' => 6,
            'max_sauces' => 1,
        ]);

        $variant2 = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Porción 12 uds',
            'price' => 55.00,
            'wings_count' => 12,
            'max_sauces' => 2,
        ]);

        $orderService = app(OrderService::class);
        $order = $orderService->createOrder(
            $branch->id,
            $table->id,
            $user->id,
            'Nota inicial',
            'dine_in'
        );

        $orderService->addItem($order, [
            'product_variant_id' => $variant1->id,
            'quantity' => 1,
            'notes' => 'Poco picante'
        ]);

        $this->assertEquals(30.00, (float) $order->fresh()->total);

        $this->actingAs($user);

        // Probar dispatch desde TableGrid
        Livewire::test(TableGrid::class)
            ->set('selectedTableForAction', $table)
            ->call('editTableOrder')
            ->assertDispatched('edit-order', orderId: $order->id);

        // Probar carga y edición en OrderBuilder
        Livewire::test(OrderBuilder::class)
            ->dispatch('edit-order', orderId: $order->id)
            ->assertSet('editingOrderId', $order->id)
            ->assertSet('orderNotes', 'Nota inicial')
            ->assertCount('cart', 1)
            // Añadir variant2 al carrito
            ->call('addVariant', $variant2->id)
            ->assertCount('cart', 2)
            // Guardar cambios
            ->call('updateOrder')
            ->assertSet('editingOrderId', null)
            ->assertDispatched('order-saved');

        // Verificar DB
        $updatedOrder = $order->fresh(['items']);
        $this->assertEquals(2, $updatedOrder->items->count());
        $this->assertEquals(85.00, (float) $updatedOrder->total);
    }
}
