<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Modules\Menu\Models\Category;
use App\Modules\Menu\Models\Sauce;

/**
 * Web pública del restaurante (alitasdelavieja.com).
 *
 * El menú se arma desde la base: si el dueño cambia un precio en el panel,
 * la web queda actualizada sola. Un producto con precio 0 en una sucursal
 * no se ofrece ahí (misma regla que usa el POS).
 */
class PublicSiteController extends Controller
{
    public function home()
    {
        $sucursales = Branch::active()->orderBy('id')->get();

        $categorias = Category::where('is_active', true)
            ->with(['products' => function ($q) {
                $q->where('is_active', true)
                    ->with('variants.prices')
                    ->orderBy('name');
            }])
            ->get()
            ->map(function (Category $categoria) use ($sucursales) {
                $productos = $categoria->products
                    ->map(function ($producto) use ($sucursales) {
                        $variantes = $producto->variants
                            ->where('is_active', true)
                            ->map(function ($variante) use ($sucursales) {
                                // Precio por sucursal: el propio si existe, si no el general.
                                $precios = [];
                                foreach ($sucursales as $sucursal) {
                                    $propio = $variante->prices
                                        ->firstWhere('branch_id', $sucursal->id)?->price;
                                    $precios[$sucursal->id] = (float) ($propio ?? $variante->price);
                                }

                                return [
                                    'nombre'  => $variante->name,
                                    'precios' => $precios,
                                ];
                            })
                            // Se descarta la variante que no se vende en ninguna sucursal.
                            ->filter(fn ($v) => collect($v['precios'])->max() > 0)
                            ->values();

                        return [
                            'nombre'      => $producto->name,
                            'descripcion' => $producto->description,
                            'variantes'   => $variantes,
                        ];
                    })
                    ->filter(fn ($p) => $p['variantes']->isNotEmpty())
                    ->values();

                return [
                    'nombre'    => $categoria->name,
                    'productos' => $productos,
                ];
            })
            ->filter(fn ($c) => $c['productos']->isNotEmpty())
            ->values();

        return view('public.home', [
            'sucursales' => $sucursales,
            'categorias' => $categorias,
            'salsas'     => Sauce::where('is_active', true)->orderBy('spice_level')->get(),
        ]);
    }
}
