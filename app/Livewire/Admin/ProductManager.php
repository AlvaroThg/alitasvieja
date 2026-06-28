<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Modules\Menu\Models\Product;
use App\Modules\Menu\Models\Category;
use Illuminate\Validation\Rule;
use App\Modules\Menu\Models\ProductPrice;

class ProductManager extends Component
{
    public $products;
    public $categories;
    
    // Modal state
    public $showModal = false;
    public $isEdit = false;
    public $productId;

    // Eliminar
    public $showDeleteModal = false;
    public $deleteProductId = null;
    public $deleteProductName = '';

    // Product form fields
    public $category_id;
    public $name;
    public $description;
    public $is_wings = false;
    public $tracks_stock = false;
    public $has_sauces = false;
    public $is_active = true;

    // Variants
    public $variants = [];

    public $branches = [];

    public function mount()
    {
        $this->loadData();
        $this->categories = Category::where('is_active', true)->get();
        $this->branches = \App\Models\Branch::all();
    }

    public function loadData()
    {
        $this->products = Product::with(['category', 'variants.prices'])->get();
    }

    public function create()
    {
        $this->resetFields();
        $this->isEdit = false;
        // Una fila de precio por defecto: el producto puede tener un solo precio
        // (sin variantes). Si necesita variantes, se agregan con "+ Agregar Variante".
        $this->addVariant();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->resetFields();
        $this->isEdit = true;
        $product = Product::with('variants.prices')->find($id);
        
        $this->productId = $product->id;
        $this->category_id = $product->category_id;
        $this->name = $product->name;
        $this->description = $product->description;
        $this->is_wings = (bool)$product->is_wings;
        $this->tracks_stock = (bool)$product->tracks_stock;
        $this->has_sauces = (bool)$product->has_sauces;
        $this->is_active = (bool)$product->is_active;

        foreach ($product->variants as $variant) {
            $prices = [];
            foreach ($variant->prices as $p) {
                $prices[$p->branch_id] = $p->price;
            }

            $this->variants[] = [
                'id' => $variant->id,
                'name' => $variant->name,
                'wings_count' => $variant->wings_count,
                'max_sauces' => $variant->max_sauces,
                'price' => $variant->price,
                'branch_prices' => $prices,
            ];
        }

        $this->showModal = true;
    }

    public function addVariant()
    {
        $this->variants[] = [
            'id' => null,
            'name' => '',
            'wings_count' => 0,
            'max_sauces' => 0,
            'price' => 0,
            'branch_prices' => [],
        ];
    }

    public function removeVariant($index)
    {
        unset($this->variants[$index]);
        $this->variants = array_values($this->variants);
    }

    public function confirmDeleteProduct($id)
    {
        $product = Product::find($id);
        if (!$product) return;
        $this->deleteProductId = $id;
        $this->deleteProductName = $product->name;
        $this->showDeleteModal = true;
    }

    public function deleteProduct()
    {
        $product = Product::with('variants')->find($this->deleteProductId);
        if (!$product) {
            $this->showDeleteModal = false;
            return;
        }

        $variantIds = $product->variants->pluck('id')->all();

        // Si el producto ya fue usado en pedidos, no se borra (se debe desactivar).
        $usedInOrders = \Illuminate\Support\Facades\DB::table('order_items')
            ->whereIn('product_variant_id', $variantIds)->exists();

        if ($usedInOrders) {
            $this->showDeleteModal = false;
            session()->flash('error', 'No se puede eliminar "' . $product->name . '": ya tiene pedidos registrados. Desactívalo en su lugar.');
            return;
        }

        foreach ($product->variants as $variant) {
            ProductPrice::where('product_variant_id', $variant->id)->delete();
            \App\Modules\Inventory\Models\Inventory::where('product_variant_id', $variant->id)->delete();
            \App\Modules\Inventory\Models\InventoryMovement::where('product_variant_id', $variant->id)->delete();
            $variant->delete();
        }
        $product->delete();

        $this->showDeleteModal = false;
        $this->deleteProductId = null;
        $this->loadData();
        session()->flash('message', 'Producto eliminado.');
    }

    public function save()
    {
        $this->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => ['required', 'string', 'max:255', Rule::unique('products', 'name')->ignore($this->productId)],
            'variants' => 'required|array|min:1',
            // El nombre de la variante es opcional (un producto simple no necesita nombre de variante).
            'variants.*.name' => 'nullable|string|max:255',
            'variants.*.price' => 'nullable|numeric|min:0',
            'variants.*.branch_prices.*' => 'nullable|numeric|min:0',
        ], [
            'name.unique' => 'Ya existe un producto con ese nombre.',
            'variants.*.price.min' => 'El precio no puede ser negativo.',
            'variants.*.branch_prices.*.min' => 'El precio por sucursal no puede ser negativo.',
        ]);

        // Cada variante debe tener al menos un precio (general o por sucursal).
        foreach ($this->variants as $i => $v) {
            $hasGeneral = is_numeric($v['price'] ?? null) && (float) $v['price'] > 0;
            $hasBranch = collect($v['branch_prices'] ?? [])->contains(fn ($p) => is_numeric($p) && (float) $p > 0);
            if (!$hasGeneral && !$hasBranch) {
                $this->addError("variants.{$i}.price", 'Debe ingresar el precio del producto.');
                return;
            }
        }

        $productData = [
            'category_id' => $this->category_id,
            'name' => $this->name,
            'description' => $this->description,
            'is_wings' => $this->is_wings,
            'tracks_stock' => $this->tracks_stock,
            'has_sauces' => $this->has_sauces,
            'is_active' => $this->is_active,
        ];

        if ($this->isEdit) {
            $product = Product::find($this->productId);
            $product->update($productData);

            // Sync variants
            $existingVariantIds = collect($this->variants)->pluck('id')->filter()->toArray();
            $product->variants()->whereNotIn('id', $existingVariantIds)->delete();

            foreach ($this->variants as $variantData) {
                $clean = $this->cleanVariant($variantData);
                if ($variantData['id']) {
                    $variant = $product->variants()->find($variantData['id']);
                    $variant->update($clean);
                } else {
                    $variant = $product->variants()->create($clean);
                }
                $this->saveBranchPrices($variant, $variantData['branch_prices'] ?? []);
            }
        } else {
            $product = Product::create($productData);
            foreach ($this->variants as $variantData) {
                $variant = $product->variants()->create($this->cleanVariant($variantData));
                $this->saveBranchPrices($variant, $variantData['branch_prices'] ?? []);
            }
        }

        $this->showModal = false;
        $this->loadData();
    }

    /**
     * Normaliza los datos de una variante: nombre por defecto "Único" (para
     * productos simples sin variantes) y precio 0 si se deja en blanco.
     */
    private function cleanVariant(array $variantData): array
    {
        $name = trim((string) ($variantData['name'] ?? ''));
        $price = $variantData['price'];

        return [
            'name'        => $name !== '' ? $name : 'Único',
            'wings_count' => (int) ($variantData['wings_count'] ?? 0),
            'max_sauces'  => (int) ($variantData['max_sauces'] ?? 0),
            'price'       => ($price === '' || $price === null) ? 0 : (float) $price,
        ];
    }

    private function saveBranchPrices($variant, $branchPrices)
    {
        foreach ($branchPrices as $branchId => $price) {
            if ($price !== null && $price !== '') {
                ProductPrice::updateOrCreate(
                    ['product_variant_id' => $variant->id, 'branch_id' => $branchId],
                    ['price' => $price]
                );
            } else {
                ProductPrice::where('product_variant_id', $variant->id)
                    ->where('branch_id', $branchId)
                    ->delete();
            }
        }
    }

    public function resetFields()
    {
        $this->productId = null;
        $this->category_id = null;
        $this->name = '';
        $this->description = '';
        $this->is_wings = false;
        $this->tracks_stock = false;
        $this->has_sauces = false;
        $this->is_active = true;
        $this->variants = [];
    }

    public function render()
    {
        return view('livewire.admin.product-manager');
    }
}
