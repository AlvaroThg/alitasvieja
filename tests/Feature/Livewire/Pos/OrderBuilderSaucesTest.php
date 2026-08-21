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
}
