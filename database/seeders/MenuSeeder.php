<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Modules\Menu\Models\Category;
use App\Modules\Menu\Models\Product;
use App\Modules\Menu\Models\ProductPrice;
use App\Modules\Menu\Models\ProductVariant;
use App\Modules\Menu\Models\Sauce;
use Illuminate\Database\Seeder;

/**
 * Menú real de Alitas La Vieja (carta del cliente).
 *
 *   php artisan db:seed --class=MenuSeeder
 *
 * Idempotente: se puede volver a ejecutar; actualiza precios y datos en vez
 * de duplicar. Los productos/salsas que NO están en esta carta se DESACTIVAN
 * (no se borran, para no perder el historial de pedidos).
 *
 * Precios por sucursal: 'cbba' (Cochabamba) y 'tja' (Tarija).
 * Un precio 0 en una sucursal OCULTA ese ítem en el POS de esa sucursal.
 */
class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // Se busca por slug y, como respaldo, por ciudad (en algunos entornos el
        // slug lleva sufijo).
        $cbba = Branch::where('slug', 'cbba')->orWhere('city', 'Cochabamba')->first();
        $tja  = Branch::where('slug', 'tja')->orWhere('city', 'Tarija')->first();

        if (!$cbba || !$tja) {
            $this->command->error('No se encontraron las sucursales de Cochabamba y Tarija. Abortado.');
            return;
        }

        $this->command->info("Sucursales: {$cbba->name} (#{$cbba->id}) y {$tja->name} (#{$tja->id}).");

        // ─── Carta ────────────────────────────────────────────────────────
        // 'price'  = precio general (se usa si la sucursal no tiene uno propio)
        // 'cbba' / 'tja' = precio específico de esa sucursal (0 = no se vende ahí)
        $menu = [
            'Alitas' => [
                'description' => 'Alitas por piezas, con porción de papa y salsas a elección',
                'products' => [
                    [
                        'name' => 'Alitas',
                        'description' => 'Piezas de alitas + porción de papa + salsas a elección',
                        'is_wings' => true,
                        'has_sauces' => true,
                        'max_sauces' => 4,
                        'variants' => [
                            ['name' => '4 Piezas',  'wings_count' => 4,  'max_sauces' => 1, 'price' => 20],
                            ['name' => '6 Piezas',  'wings_count' => 6,  'max_sauces' => 1, 'price' => 29],
                            ['name' => '8 Piezas',  'wings_count' => 8,  'max_sauces' => 2, 'price' => 39],
                            ['name' => '12 Piezas', 'wings_count' => 12, 'max_sauces' => 2, 'price' => 55],
                            ['name' => '16 Piezas', 'wings_count' => 16, 'max_sauces' => 3, 'price' => 72],
                            ['name' => '24 Piezas', 'wings_count' => 24, 'max_sauces' => 4, 'price' => 90],
                        ],
                    ],
                ],
            ],

            'Picadas' => [
                'description' => 'Papas fritas y picadas',
                'products' => [
                    [
                        'name' => 'Porción de Papas Fritas',
                        'description' => 'Porción de papas fritas',
                        'variants' => [
                            ['name' => 'Natural',        'price' => 10],
                            ['name' => 'Con limón',      'price' => 12],
                            ['name' => 'Con paprika',    'price' => 12],
                            ['name' => 'Con queso fundido', 'price' => 14],
                        ],
                    ],
                    [
                        'name' => 'Chicken Feet (Patitas)',
                        'description' => '7 patitas + porción de papas y una salsa a elección',
                        'has_sauces' => true,
                        'max_sauces' => 1,
                        'variants' => [
                            ['name' => '7 Piezas', 'max_sauces' => 1, 'price' => 20],
                        ],
                    ],
                    [
                        'name' => 'Chicken Fingers (Deditos)',
                        'description' => '5 deditos + porción de papas y una salsa a elección',
                        'has_sauces' => true,
                        'max_sauces' => 1,
                        'variants' => [
                            ['name' => '5 Piezas', 'max_sauces' => 1, 'price' => 20],
                        ],
                    ],
                ],
            ],

            'Bebidas' => [
                'description' => 'Gaseosas, jugos y bebidas',
                'products' => [
                    [
                        'name' => 'Gaseosa',
                        'description' => 'Coca Cola, Fanta o Sprite',
                        'variants' => [
                            ['name' => 'Personal',    'price' => 4],
                            ['name' => 'Popular',     'price' => 8],
                            ['name' => 'Litro y 1/2', 'price' => 11],
                        ],
                    ],
                    [
                        'name' => 'Jugo Artesanal de Pelón',
                        'description' => 'Jugo artesanal de pelón',
                        'variants' => [
                            ['name' => 'Vaso',      'price' => 3],
                            ['name' => '1/2 Litro', 'price' => 6],
                            ['name' => '1 Litro',   'price' => 12],
                        ],
                    ],
                    [
                        'name' => 'Copa de Vino Artesanal',
                        'description' => 'Copa de vino artesanal',
                        'variants' => [
                            // Mismo producto, precio distinto por sucursal.
                            ['name' => 'Copa', 'price' => 10, 'cbba' => 20, 'tja' => 10],
                        ],
                    ],
                    [
                        'name' => 'Cerveza en Lata',
                        'description' => 'Cerveza en lata (unidad)',
                        'variants' => [
                            ['name' => 'Lata', 'price' => 15],
                        ],
                    ],
                    [
                        'name' => 'Chuflay',
                        'description' => 'Chuflay',
                        'variants' => [
                            // Solo Cochabamba: en Tarija va en 0 para que no aparezca.
                            ['name' => 'Vaso', 'price' => 20, 'cbba' => 20, 'tja' => 0],
                        ],
                    ],
                ],
            ],

            'Helados' => [
                'description' => 'Helados artesanales',
                'products' => [
                    [
                        'name' => 'Helado',
                        'description' => 'Helado artesanal',
                        'tracks_stock' => true,
                        'variants' => [
                            // Solo Tarija: en Cochabamba van en 0 para que no aparezcan.
                            ['name' => 'Frutilla', 'price' => 12, 'cbba' => 0, 'tja' => 12],
                            ['name' => 'Durazno',  'price' => 12, 'cbba' => 0, 'tja' => 12],
                        ],
                    ],
                ],
            ],
        ];

        // ─── Salsas de la carta ───────────────────────────────────────────
        $salsas = [
            ['name' => 'Barbacoa',            'spice_level' => 1],
            ['name' => 'La Vieja Picante',    'spice_level' => 3],
            ['name' => 'Vieja Extra Picante', 'spice_level' => 5],
            ['name' => 'Doña Choca',          'spice_level' => 1],
            ['name' => 'Morenaza',            'spice_level' => 1],
            ['name' => 'Albina de Ajo',       'spice_level' => 1],
        ];

        // ─── Carga ────────────────────────────────────────────────────────
        $branchIds = ['cbba' => $cbba->id, 'tja' => $tja->id];
        $productosCargados = [];

        foreach ($menu as $categoryName => $categoryData) {
            $category = Category::firstOrCreate(
                ['name' => $categoryName],
                ['description' => $categoryData['description'], 'is_active' => true]
            );
            $category->update(['is_active' => true]);

            foreach ($categoryData['products'] as $p) {
                $product = Product::updateOrCreate(
                    ['name' => $p['name']],
                    [
                        'category_id'  => $category->id,
                        'description'  => $p['description'] ?? null,
                        'is_wings'     => $p['is_wings'] ?? false,
                        'tracks_stock' => $p['tracks_stock'] ?? false,
                        'has_sauces'   => $p['has_sauces'] ?? false,
                        'max_sauces'   => $p['max_sauces'] ?? 0,
                        'is_active'    => true,
                    ]
                );
                $productosCargados[] = $product->id;

                foreach ($p['variants'] as $v) {
                    $variant = ProductVariant::updateOrCreate(
                        ['product_id' => $product->id, 'name' => $v['name']],
                        [
                            'wings_count' => $v['wings_count'] ?? 0,
                            'max_sauces'  => $v['max_sauces'] ?? 0,
                            'price'       => $v['price'],
                            'is_active'   => true,
                        ]
                    );

                    // Precio por sucursal solo si la carta lo define.
                    foreach ($branchIds as $slug => $branchId) {
                        if (array_key_exists($slug, $v)) {
                            ProductPrice::updateOrCreate(
                                ['product_variant_id' => $variant->id, 'branch_id' => $branchId],
                                ['price' => $v[$slug]]
                            );
                        }
                    }
                }

                // Quitar variantes que ya no están en la carta.
                $nombresCarta = array_column($p['variants'], 'name');
                $product->variants()->whereNotIn('name', $nombresCarta)->update(['is_active' => false]);
            }
        }

        // Desactivar productos que no son de la carta (no se borran: hay historial).
        $fueraDeCarta = Product::whereNotIn('id', $productosCargados)->where('is_active', true)->get();
        foreach ($fueraDeCarta as $p) {
            $p->update(['is_active' => false]);
            $this->command->warn("Producto fuera de carta desactivado: {$p->name}");
        }

        // Salsas: crear/activar las de la carta y desactivar el resto.
        $idsSalsas = [];
        foreach ($salsas as $s) {
            $sauce = Sauce::updateOrCreate(
                ['name' => $s['name']],
                ['spice_level' => $s['spice_level'], 'is_active' => true]
            );
            $idsSalsas[] = $sauce->id;
        }
        $salsasFuera = Sauce::whereNotIn('id', $idsSalsas)->where('is_active', true)->get();
        foreach ($salsasFuera as $s) {
            $s->update(['is_active' => false]);
            $this->command->warn("Salsa fuera de carta desactivada: {$s->name}");
        }

        $this->command->info('Menú cargado: ' . count($productosCargados) . ' productos y ' . count($idsSalsas) . ' salsas.');
        $this->command->info('Tarija: helados a 12 Bs · Cochabamba: sin helados, con Chuflay y copa de vino a 20 Bs.');
    }
}
