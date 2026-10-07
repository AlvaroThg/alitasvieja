<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Modules\Menu\Models\ProductVariant;
use App\Modules\Orders\Services\WingSauceValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CochabambaSauceChargeTest extends TestCase
{
    use RefreshDatabase;

    public function test_cochabamba_coated_wings_charge_5_bs_on_all_portions()
    {
        $cbba = Branch::create([
            'name' => 'Sucursal Cochabamba',
            'slug' => 'cbba',
            'address' => 'Av. Heroinas',
            'city' => 'Cochabamba',
            'phone' => '4000000',
            'is_active' => true,
        ]);

        $tja = Branch::create([
            'name' => 'Sucursal Tarija',
            'slug' => 'tja',
            'address' => 'Calle Madrid',
            'city' => 'Tarija',
            'phone' => '6000000',
            'is_active' => true,
        ]);

        $validator = app(WingSauceValidator::class);

        $v6 = new ProductVariant(['wings_count' => 6, 'max_sauces' => 1]);
        $v8 = new ProductVariant(['wings_count' => 8, 'max_sauces' => 1]);
        $v12 = new ProductVariant(['wings_count' => 12, 'max_sauces' => 2]);
        $v16 = new ProductVariant(['wings_count' => 16, 'max_sauces' => 2]);
        $v24 = new ProductVariant(['wings_count' => 24, 'max_sauces' => 3]);
        $vNueva = new ProductVariant(['wings_count' => 50, 'max_sauces' => 5]);

        // En Cochabamba: TODA variante de alitas bañada suma 5.00 Bs
        $this->assertEquals(5.00, $validator->validate($v6, $cbba->id, [
            ['sauce_id' => 1, 'quantity' => 6, 'is_coated' => true]
        ]));

        $this->assertEquals(5.00, $validator->validate($v8, $cbba->id, [
            ['sauce_id' => 1, 'quantity' => 8, 'is_coated' => true]
        ]));

        $this->assertEquals(5.00, $validator->validate($v12, $cbba->id, [
            ['sauce_id' => 1, 'quantity' => 12, 'is_coated' => true]
        ]));

        $this->assertEquals(5.00, $validator->validate($v16, $cbba->id, [
            ['sauce_id' => 1, 'quantity' => 16, 'is_coated' => true]
        ]));

        $this->assertEquals(5.00, $validator->validate($v24, $cbba->id, [
            ['sauce_id' => 1, 'quantity' => 24, 'is_coated' => true]
        ]));

        $this->assertEquals(5.00, $validator->validate($vNueva, $cbba->id, [
            ['sauce_id' => 1, 'quantity' => 50, 'is_coated' => true]
        ]));

        // Si son solo salsas APARTE -> 0.00 extra
        $this->assertEquals(0.00, $validator->validate($v12, $cbba->id, [
            ['sauce_id' => 1, 'quantity' => 12, 'is_coated' => false]
        ]));

        // Si la cantidad comprada es 2 y ambas porciones son bañadas -> 10.00 extra (5.00 x 2)
        $this->assertEquals(10.00, $validator->validate($v12, $cbba->id, [
            ['sauce_id' => 1, 'quantity' => 24, 'is_coated' => true]
        ], 2));

        // Si la cantidad comprada es 2, pero solo 1 porción es bañada (12 alitas) y 1 porción es aparte (12 alitas) -> sólo 5.00 extra
        $this->assertEquals(5.00, $validator->validate($v12, $cbba->id, [
            ['sauce_id' => 1, 'quantity' => 12, 'is_coated' => true],
            ['sauce_id' => 1, 'quantity' => 12, 'is_coated' => false]
        ], 2));

        // En Tarija: siempre 0.00 extra
        $this->assertEquals(0.00, $validator->validate($v12, $tja->id, [
            ['sauce_id' => 1, 'quantity' => 12, 'is_coated' => true]
        ]));
    }

    public function test_picadas_configurable_coated_sauces_charge()
    {
        $cbba = Branch::create([
            'name' => 'Sucursal Cochabamba',
            'slug' => 'cbba',
            'address' => 'Av. Heroinas',
            'city' => 'Cochabamba',
            'phone' => '4000000',
            'is_active' => true,
        ]);

        $category = \App\Modules\Menu\Models\Category::create(['name' => 'Picadas', 'is_active' => true]);

        // Picada con cobro de bañadas desactivado (default false)
        $p1 = \App\Modules\Menu\Models\Product::create([
            'category_id' => $category->id,
            'name' => 'Picada Familiar Sin Recargo',
            'is_wings' => false,
            'has_sauces' => true,
            'charge_coated_sauces' => false,
            'is_active' => true,
        ]);
        $v1 = \App\Modules\Menu\Models\ProductVariant::create([
            'product_id' => $p1->id,
            'name' => 'Familiar',
            'wings_count' => 10,
            'max_sauces' => 2,
            'price' => 50,
        ]);

        // Picada con cobro de bañadas activado por el dueño
        $p2 = \App\Modules\Menu\Models\Product::create([
            'category_id' => $category->id,
            'name' => 'Picada Especial Con Recargo',
            'is_wings' => false,
            'has_sauces' => true,
            'charge_coated_sauces' => true,
            'is_active' => true,
        ]);
        $v2 = \App\Modules\Menu\Models\ProductVariant::create([
            'product_id' => $p2->id,
            'name' => 'Especial',
            'wings_count' => 10,
            'max_sauces' => 2,
            'price' => 60,
        ]);

        $validator = app(WingSauceValidator::class);

        // Picada 1 (charge_coated_sauces = false) bañada -> 0.00 extra
        $this->assertEquals(0.00, $validator->validate($v1, $cbba->id, [
            ['sauce_id' => 1, 'quantity' => 10, 'is_coated' => true]
        ]));

        // Picada 2 (charge_coated_sauces = true) bañada -> 5.00 extra
        $this->assertEquals(5.00, $validator->validate($v2, $cbba->id, [
            ['sauce_id' => 1, 'quantity' => 10, 'is_coated' => true]
        ]));
    }

    public function test_non_wings_without_wing_counts_does_not_throw_pieces_error()
    {
        $cbba = Branch::create([
            'name' => 'Sucursal Cochabamba',
            'slug' => 'cbba',
            'city' => 'Cochabamba',
            'is_active' => true,
        ]);

        $category = \App\Modules\Menu\Models\Category::create(['name' => 'Picadas', 'is_active' => true]);

        $p = \App\Modules\Menu\Models\Product::create([
            'category_id' => $category->id,
            'name' => 'Picada Mixta',
            'is_wings' => false,
            'has_sauces' => true,
            'charge_coated_sauces' => true,
            'is_active' => true,
        ]);

        $v = \App\Modules\Menu\Models\ProductVariant::create([
            'product_id' => $p->id,
            'name' => 'Porción Única',
            'wings_count' => 0,
            'max_sauces' => 2,
            'price' => 45,
        ]);

        $validator = app(WingSauceValidator::class);

        // No debe lanzar excepción por piezas cuando wings_count es 0
        $charge = $validator->validate($v, $cbba->id, [
            ['sauce_id' => 1, 'quantity' => 1, 'is_coated' => true]
        ]);

        $this->assertEquals(5.00, $charge);
    }

    public function test_owner_can_configure_custom_sauce_coated_price_per_branch()
    {
        // Crear sucursal en Santa Cruz con recargo de 8.50 Bs
        $scz = Branch::create([
            'name' => 'Sucursal Santa Cruz',
            'slug' => 'scz',
            'city' => 'Santa Cruz',
            'sauce_coated_price' => 8.50,
            'is_active' => true,
        ]);

        $validator = app(WingSauceValidator::class);
        $v12 = new ProductVariant(['wings_count' => 12, 'max_sauces' => 2]);

        // En Santa Cruz con recargo de 8.50 Bs: alitas bañadas suma 8.50 Bs
        $charge = $validator->validate($v12, $scz->id, [
            ['sauce_id' => 1, 'quantity' => 12, 'is_coated' => true]
        ]);
        $this->assertEquals(8.50, $charge);

        // Si el dueño cambia la sucursal de Cochabamba a 3.00 Bs
        $cbba = Branch::create([
            'name' => 'Sucursal Cochabamba',
            'slug' => 'cbba',
            'city' => 'Cochabamba',
            'sauce_coated_price' => 3.00,
            'is_active' => true,
        ]);

        $chargeCbba = $validator->validate($v12, $cbba->id, [
            ['sauce_id' => 1, 'quantity' => 12, 'is_coated' => true]
        ]);
        $this->assertEquals(3.00, $chargeCbba);
    }

    public function test_owner_can_configure_custom_coated_price_per_variant()
    {
        $branch = Branch::create(['name' => 'Sucursal Principal', 'slug' => 'principal', 'city' => 'Cochabamba', 'is_active' => true]);
        $category = \App\Modules\Menu\Models\Category::create(['name' => 'Alitas Cat', 'is_active' => true]);
        $product = \App\Modules\Menu\Models\Product::create([
            'category_id' => $category->id,
            'name' => 'Alitas Especiales',
            'is_wings' => true,
            'has_sauces' => true,
            'charge_coated_sauces' => true,
            'is_active' => true,
        ]);

        $validator = app(WingSauceValidator::class);

        // Variante 1: 6 Piezas con recargo de 3.50 Bs por bañadas
        $v6 = ProductVariant::create(['product_id' => $product->id, 'name' => '6 Piezas', 'wings_count' => 6, 'max_sauces' => 1, 'coated_price' => 3.50, 'price' => 29]);

        // Variante 2: 24 Piezas con recargo de 10.00 Bs por bañadas
        $v24 = ProductVariant::create(['product_id' => $product->id, 'name' => '24 Piezas', 'wings_count' => 24, 'max_sauces' => 3, 'coated_price' => 10.00, 'price' => 90]);

        // Variante 3: Sin recargo por bañadas (coated_price = 0.00)
        $vFree = ProductVariant::create(['product_id' => $product->id, 'name' => '4 Piezas', 'wings_count' => 4, 'max_sauces' => 1, 'coated_price' => 0.00, 'price' => 20]);

        // 1. Recargo de 6 piezas -> 3.50 Bs
        $c1 = $validator->validate($v6, $branch->id, [['sauce_id' => 1, 'quantity' => 6, 'is_coated' => true]]);
        $this->assertEquals(3.50, $c1);

        // 2. Recargo de 24 piezas -> 10.00 Bs
        $c2 = $validator->validate($v24, $branch->id, [['sauce_id' => 1, 'quantity' => 24, 'is_coated' => true]]);
        $this->assertEquals(10.00, $c2);

        // 3. Recargo de variante sin recargo -> 0.00 Bs
        $c3 = $validator->validate($vFree, $branch->id, [['sauce_id' => 1, 'quantity' => 4, 'is_coated' => true]]);
        $this->assertEquals(0.00, $c3);
    }

    public function test_owner_can_configure_custom_coated_price_per_variant_and_branch()
    {
        $cbba = Branch::create(['name' => 'Cochabamba', 'slug' => 'cbba', 'city' => 'Cochabamba', 'is_active' => true]);
        $tja = Branch::create(['name' => 'Tarija', 'slug' => 'tja', 'city' => 'Tarija', 'is_active' => true]);

        $category = \App\Modules\Menu\Models\Category::create(['name' => 'Alitas Cat 2', 'is_active' => true]);
        $product = \App\Modules\Menu\Models\Product::create([
            'category_id' => $category->id,
            'name' => 'Alitas Tradicionales 2',
            'is_wings' => true,
            'has_sauces' => true,
            'charge_coated_sauces' => true,
            'is_active' => true,
        ]);

        $variant = ProductVariant::create(['product_id' => $product->id, 'name' => '12 Piezas', 'wings_count' => 12, 'max_sauces' => 2, 'price' => 55]);

        // En Cochabamba: recargo de 5.00 Bs
        \App\Modules\Menu\Models\ProductPrice::create([
            'product_variant_id' => $variant->id,
            'branch_id' => $cbba->id,
            'price' => 55,
            'coated_price' => 5.00,
        ]);

        // En Tarija: recargo de 0.00 Bs
        \App\Modules\Menu\Models\ProductPrice::create([
            'product_variant_id' => $variant->id,
            'branch_id' => $tja->id,
            'price' => 55,
            'coated_price' => 0.00,
        ]);

        $validator = app(WingSauceValidator::class);

        $chargeCbba = $validator->validate($variant, $cbba->id, [['sauce_id' => 1, 'quantity' => 12, 'is_coated' => true]]);
        $this->assertEquals(5.00, $chargeCbba);

        $chargeTja = $validator->validate($variant, $tja->id, [['sauce_id' => 1, 'quantity' => 12, 'is_coated' => true]]);
        $this->assertEquals(0.00, $chargeTja);
    }
}
