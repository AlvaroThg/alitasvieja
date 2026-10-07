<?php

namespace Tests\Feature\Livewire\Pos;

use App\Livewire\Pos\OrderBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrderBuilderSaucesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Prueba que las cantidades de alitas bañadas y salsas aparte no excedan el límite de alitas del producto.
     */
    public function test_sauce_counts_respect_wings_limit()
    {
        Livewire::test(OrderBuilder::class)
            // Simulamos que el producto tiene 6 alitas
            ->set('tempProductWingsCount', 6)
            ->set('tempSelectedSauceIds', [1, 2])
            
            // Añadimos 3 bañadas de la salsa 1
            ->call('incrementSauceWings', 1)
            ->call('incrementSauceWings', 1)
            ->call('incrementSauceWings', 1)
            ->assertSet('tempSauceWingCounts', [1 => 3])
            
            // Añadimos 2 aparte de la salsa 2
            ->call('incrementSauceSide', 2)
            ->call('incrementSauceSide', 2)
            ->assertSet('tempSauceSideCounts', [2 => 2])
            
            // Llevamos al límite: añadimos 1 bañada más a la salsa 1 (total = 6)
            ->call('incrementSauceWings', 1)
            ->assertSet('tempSauceWingCounts', [1 => 4])
            
            // Intentamos añadir 1 más, no debería permitirlo porque el total ya es 6
            ->call('incrementSauceSide', 2)
            ->assertSet('tempSauceSideCounts', [2 => 2]) // Se mantiene en 2
            ->call('incrementSauceWings', 1)
            ->assertSet('tempSauceWingCounts', [1 => 4]); // Se mantiene en 4
    }

    /**
     * Prueba que se puede decrementar correctamente.
     */
    /**
     * Prueba que se puede decrementar correctamente.
     */
    public function test_sauce_counts_can_decrement()
    {
        Livewire::test(OrderBuilder::class)
            ->set('tempProductWingsCount', 6)
            ->set('tempSelectedSauceIds', [1])
            ->set('tempSauceWingCounts', [1 => 2])
            ->set('tempSauceSideCounts', [1 => 2])
            
            ->call('decrementSauceWings', 1)
            ->assertSet('tempSauceWingCounts', [1 => 1])
            
            ->call('decrementSauceSide', 1)
            ->assertSet('tempSauceSideCounts', [1 => 1])
            
            // Intenta bajar a menos de 0
            ->call('decrementSauceWings', 1)
            ->call('decrementSauceWings', 1)
            ->assertSet('tempSauceWingCounts', [1 => 0]);
    }

    public function test_quantity_multiplies_sauce_limits()
    {
        Livewire::test(OrderBuilder::class)
            ->set('cart', [
                [
                    'id' => 1,
                    'quantity' => 2,
                    'max_sauces' => 1,
                    'wings_count' => 6,
                    'has_sauces' => true,
                    'sauces' => [],
                    'product_name' => 'Alitas',
                    'variant_name' => 'Clasicas',
                    'price' => 20,
                ]
            ])
            ->call('openSauceModal', 0)
            ->assertSet('tempProductMaxSauces', 2)
            ->assertSet('tempProductWingsCount', 12);
    }

    public function test_sauce_counts_with_nested_arrays_does_not_throw_array_sum_exception()
    {
        Livewire::test(OrderBuilder::class)
            ->set('tempItemQuantity', 1)
            ->set('tempProductWingsCount', 6)
            ->set('tempSauceWingCounts', [0 => [1 => 3]])
            ->set('tempSauceSideCounts', [0 => [1 => 1]])
            ->call('incrementSauceWings', 1)
            ->assertSet('tempSauceWingCounts', [0 => [1 => 4]]);
    }

    public function test_bill_amount_and_cash_change_calculation()
    {
        Livewire::test(OrderBuilder::class)
            ->set('cart', [
                [
                    'id' => 1,
                    'variant_id' => 1,
                    'product_name' => 'Alitas',
                    'variant_name' => '6 Piezas',
                    'quantity' => 2,
                    'price' => 25,
                    'has_sauces' => false,
                    'sauces' => [],
                ]
            ])
            ->call('setBillAmount', 100)
            ->assertSet('cashReceived', '100')
            ->assertSet('cashChange', 50.0)
            ->assertSet('cashMissing', 0.0)
            ->call('setBillAmount', 20)
            ->assertSet('cashReceived', '20')
            ->assertSet('cashChange', 0.0)
            ->assertSet('cashMissing', 30.0)
            ->call('setBillAmount', 'exact')
            ->assertSet('cashReceived', '50')
            ->assertSet('cashChange', 0.0)
            ->assertSet('cashMissing', 0.0);
    }

    public function test_fast_sauce_shortcuts_and_order_type_selection()
    {
        Livewire::test(OrderBuilder::class)
            ->call('selectOrderType', 'delivery')
            ->assertSet('orderType', 'delivery')
            ->assertSet('tableId', null)
            ->call('selectOrderType', 'dine_in')
            ->assertSet('orderType', 'dine_in')
            ->set('tempItemQuantity', 3)
            ->set('tempProductWingsCount', 18)
            ->set('tempIsWingsProduct', true)
            ->set('tempSelectedSauceIds', [1, 2])
            ->call('setAllUnitsCoated', true)
            ->assertSet('tempSauceWingCounts', [
                0 => [1 => 6],
                1 => [2 => 6],
                2 => [1 => 6],
            ])
            ->call('copyUnitOneToAll')
            ->assertSet('tempSauceWingCounts', [
                0 => [1 => 6],
                1 => [1 => 6],
                2 => [1 => 6],
            ]);
    }

    public function test_liberate_and_occupy_table_actions()
    {
        $table = \App\Models\Table::create([
            'branch_id' => 1,
            'name' => 'Mesa Test',
            'status' => 'available',
        ]);

        Livewire::test(OrderBuilder::class)
            ->call('occupyTable', $table->id);

        $this->assertEquals('occupied', $table->fresh()->status);

        Livewire::test(OrderBuilder::class)
            ->call('liberateTable', $table->id);

        $this->assertEquals('available', $table->fresh()->status);
    }

    public function test_adding_wings_variant_does_not_auto_open_sauce_modal_allowing_qty_selection_first()
    {
        $category = \App\Modules\Menu\Models\Category::create(['name' => 'Alitas', 'sort_order' => 1]);
        $product = \App\Modules\Menu\Models\Product::create([
            'category_id' => $category->id,
            'name' => 'Alitas BBQ',
            'has_sauces' => true,
            'is_wings' => true,
        ]);
        $variant = \App\Modules\Menu\Models\ProductVariant::create([
            'product_id' => $product->id,
            'name' => '6 Piezas',
            'price' => 25.00,
            'max_sauces' => 2,
            'wings_count' => 6,
        ]);

        Livewire::test(OrderBuilder::class)
            ->call('addVariant', $variant->id)
            ->assertSet('showSauceModal', false)
            ->assertCount('cart', 1)
            ->call('incrementQty', 0)
            ->assertSet('showSauceModal', false)
            ->assertSet('cart.0.quantity', 2)
            ->call('openSauceModal', 0)
            ->assertSet('showSauceModal', true)
            ->assertSet('tempItemQuantity', 2)
            ->assertSet('tempProductWingsCount', 12)
            ->assertSet('tempProductMaxSauces', 4)
            ->call('incrementTempQuantity')
            ->assertSet('tempItemQuantity', 3)
            ->assertSet('tempProductWingsCount', 18)
            ->assertSet('tempProductMaxSauces', 6);
    }

    public function test_direct_numeric_input_for_sauces_without_auto_prefill()
    {
        Livewire::test(OrderBuilder::class)
            ->set('tempItemQuantity', 1)
            ->set('tempProductWingsCount', 6)
            ->set('tempIsWingsProduct', true)
            ->set('tempSelectedSauceIds', [1, 2])
            ->call('goToSauceStep2')
            ->assertSet('sauceStep', 2)
            ->assertSet('tempSauceWingCounts', [])
            ->call('updateSauceWings', 1, 0, 4)
            ->assertSet('tempSauceWingCounts', [0 => [1 => 4]]);
    }

    public function test_multiple_units_assign_one_sauce_per_unit_when_multiple_sauces_selected()
    {
        Livewire::test(OrderBuilder::class)
            ->set('tempItemQuantity', 4)
            ->set('tempProductWingsCount', 24)
            ->set('tempIsWingsProduct', true)
            ->set('tempSelectedSauceIds', [10, 20, 30, 40])
            ->call('setAllUnitsCoated', true)
            ->assertSet('tempSauceWingCounts', [
                0 => [10 => 6],
                1 => [20 => 6],
                2 => [30 => 6],
                3 => [40 => 6],
            ]);
    }

    public function test_rendering_tickets_with_array_attributes_does_not_throw_exception()
    {
        $order = new \App\Modules\Orders\Models\Order([
            'daily_number' => 1,
            'order_number' => 'ORD-1234',
            'order_type' => 'dine_in',
            'customer_name' => ['Juan', 'Pérez'],
            'notes' => ['Extra picante', 'Sin sal'],
            'total' => 50.00,
        ]);
        $order->id = 1;
        $order->setRelation('branch', new \App\Models\Branch(['name' => 'Sucursal Central', 'city' => 'Cochabamba']));
        $order->setRelation('table', new \App\Models\Table(['name' => ['Mesa 1', 'Terraza']]));

        $item = new \App\Modules\Orders\Models\OrderItem([
            'quantity' => 2,
            'unit_price' => 25.00,
            'subtotal' => 50.00,
            'notes' => ['Poco frito'],
        ]);
        $variant = new \App\Modules\Menu\Models\ProductVariant(['name' => '6 Alitas']);
        $product = new \App\Modules\Menu\Models\Product(['name' => 'Alitas Clasicas', 'is_wings' => true]);
        $variant->setRelation('product', $product);
        $item->setRelation('productVariant', $variant);
        $item->unit_sauces = [
            0 => [['name' => ['BBQ', 'Miel'], 'qty' => 3, 'qty_side' => 0]],
            1 => [['name' => 'Búfalo', 'qty' => 3, 'qty_side' => 0]]
        ];

        $order->setRelation('items', collect([$item]));

        $kitchenHtml = view('tickets.kitchen', compact('order'))->render();
        $this->assertStringContainsString('Juan, Pérez', $kitchenHtml);
        $this->assertStringContainsString('BBQ, Miel', $kitchenHtml);

        $cashierHtml = view('tickets.cashier', compact('order'))->render();
        $this->assertStringContainsString('Juan, Pérez', $cashierHtml);
    }
}



