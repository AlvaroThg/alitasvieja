<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Branch;
use App\Modules\Promotions\Models\Promotion;
use App\Modules\Menu\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;

class PromotionManager extends Component
{
    // Filters
    public $filterBranch = '';
    public $filterType = '';

    // Modal state
    public $showModal = false;
    public $isEdit = false;
    public $promotionId;

    // Form fields
    public $branch_id = null;
    public $name = '';
    public $description = '';
    public $type = 'discount';
    public $discount_type = 'percentage';
    public $discount_value = 0;
    public $free_product_variant_id = null;
    public $free_quantity = 1;
    public $conditions_min_order = '';
    public $starts_at = '';
    public $ends_at = '';
    public $is_active = true;

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
        $promotion = Promotion::find($id);
        if (!$promotion) return;

        $this->promotionId = $promotion->id;
        $this->branch_id = $promotion->branch_id;
        $this->name = $promotion->name;
        $this->description = $promotion->description;
        $this->type = $promotion->type;
        $this->discount_type = $promotion->discount_type;
        $this->discount_value = $promotion->discount_value;
        $this->free_product_variant_id = $promotion->free_product_variant_id;
        $this->free_quantity = $promotion->free_quantity;
        $this->conditions_min_order = $promotion->conditions['min_order_total'] ?? '';
        $this->starts_at = $promotion->starts_at ? $promotion->starts_at->format('Y-m-d') : '';
        $this->ends_at = $promotion->ends_at ? $promotion->ends_at->format('Y-m-d') : '';
        $this->is_active = (bool) $promotion->is_active;

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:150',
            'type' => 'required|in:birthday,discount,combo,free_item,custom',
            'discount_type' => 'required|in:percentage,fixed,free_item',
            'discount_value' => 'nullable|numeric|min:0',
        ]);

        $conditions = [];
        if ($this->conditions_min_order !== '' && $this->conditions_min_order > 0) {
            $conditions['min_order_total'] = (float) $this->conditions_min_order;
        }

        $data = [
            'branch_id' => $this->branch_id ?: null,
            'name' => $this->name,
            'description' => $this->description ?: null,
            'type' => $this->type,
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value ?? 0,
            'free_product_variant_id' => $this->discount_type === 'free_item' ? $this->free_product_variant_id : null,
            'free_quantity' => $this->free_quantity ?? 1,
            'conditions' => !empty($conditions) ? $conditions : null,
            'starts_at' => $this->starts_at ?: null,
            'ends_at' => $this->ends_at ?: null,
            'is_active' => $this->is_active,
        ];

        if ($this->isEdit) {
            $promotion = Promotion::find($this->promotionId);
            $promotion->update($data);
        } else {
            $data['created_by'] = Auth::id();
            Promotion::create($data);
        }

        $this->showModal = false;
        session()->flash('message', $this->isEdit ? 'Promoción actualizada.' : 'Promoción creada.');
    }

    public function toggleActive($id)
    {
        $promotion = Promotion::find($id);
        if ($promotion) {
            $promotion->update(['is_active' => !$promotion->is_active]);
        }
    }

    public function resetFields()
    {
        $this->promotionId = null;
        $this->branch_id = null;
        $this->name = '';
        $this->description = '';
        $this->type = 'discount';
        $this->discount_type = 'percentage';
        $this->discount_value = 0;
        $this->free_product_variant_id = null;
        $this->free_quantity = 1;
        $this->conditions_min_order = '';
        $this->starts_at = '';
        $this->ends_at = '';
        $this->is_active = true;
    }

    public function render()
    {
        $branches = Branch::active()->get();
        $variants = ProductVariant::with('product')->get();

        $promotions = Promotion::with(['branch'])
            ->when($this->filterBranch, fn($q) => $q->where('branch_id', $this->filterBranch))
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.admin.promotion-manager', [
            'branches' => $branches,
            'promotions' => $promotions,
            'variants' => $variants,
        ]);
    }
}
