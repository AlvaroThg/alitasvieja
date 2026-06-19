<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Modules\Menu\Models\Product;
use App\Modules\Menu\Models\Category;

class ProductManager extends Component
{
    public $products;
    public $categories;
    
    // Modal state
    public $showModal = false;
    public $isEdit = false;
    public $productId;

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

    public function save()
    {
        $this->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'variants.*.name' => 'required|string|max:255',
            'variants.*.price' => 'required|numeric|min:0',
        ]);

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
                if ($variantData['id']) {
                    $variant = $product->variants()->find($variantData['id']);
                    $variant->update($variantData);
                } else {
                    $variant = $product->variants()->create($variantData);
                }
                $this->saveBranchPrices($variant, $variantData['branch_prices'] ?? []);
            }
        } else {
            $product = Product::create($productData);
            foreach ($this->variants as $variantData) {
                $variant = $product->variants()->create($variantData);
                $this->saveBranchPrices($variant, $variantData['branch_prices'] ?? []);
            }
        }

        $this->showModal = false;
        $this->loadData();
    }

    private function saveBranchPrices($variant, $branchPrices)
    {
        foreach ($branchPrices as $branchId => $price) {
            if ($price !== null && $price !== '') {
                \App\Models\ProductPrice::updateOrCreate(
                    ['product_variant_id' => $variant->id, 'branch_id' => $branchId],
                    ['price' => $price]
                );
            } else {
                \App\Models\ProductPrice::where('product_variant_id', $variant->id)
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
