<?php

namespace App\Livewire\Admin;

use App\Modules\Menu\Models\Sauce;
use Livewire\Component;

class SauceManager extends Component
{
    public $sauces;
    public $showModal = false;
    public $isEdit = false;
    public $sauceId;

    // Form fields
    public $name;
    public $spice_level = 0;
    public $is_active = true;

    // Sucursales y recargos
    public $branches = [];
    public $branchCoatedPrices = [];

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->sauces = Sauce::all();
        $this->branches = \App\Models\Branch::orderBy('name')->get();
        foreach ($this->branches as $branch) {
            $this->branchCoatedPrices[$branch->id] = (float) ($branch->sauce_coated_price ?? 0);
        }
    }

    public function saveBranchCoatedPrice($branchId)
    {
        $price = (float) ($this->branchCoatedPrices[$branchId] ?? 0);
        if ($price < 0) {
            $price = 0;
        }

        $branch = \App\Models\Branch::find($branchId);
        if ($branch) {
            $branch->update(['sauce_coated_price' => $price]);
            session()->flash('success_branch', "Recargo por alitas bañadas actualizado para {$branch->name}: Bs. " . number_format($price, 2));
        }
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
        
        $sauce = Sauce::find($id);
        $this->sauceId = $sauce->id;
        $this->name = $sauce->name;
        $this->spice_level = $sauce->spice_level;
        $this->is_active = (bool)$sauce->is_active;

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'spice_level' => 'required|integer|min:0|max:10',
        ]);

        $data = [
            'name' => $this->name,
            'spice_level' => (int)$this->spice_level,
            'is_active' => $this->is_active,
        ];

        if ($this->isEdit) {
            Sauce::find($this->sauceId)->update($data);
        } else {
            Sauce::create($data);
        }

        $this->showModal = false;
        $this->loadData();
    }

    public function toggleActive($id)
    {
        $sauce = Sauce::find($id);
        if ($sauce) {
            $sauce->update(['is_active' => !$sauce->is_active]);
            $this->loadData();
        }
    }

    public function resetFields()
    {
        $this->sauceId = null;
        $this->name = '';
        $this->spice_level = 0;
        $this->is_active = true;
    }

    public function render()
    {
        return view('livewire.admin.sauce-manager');
    }
}
