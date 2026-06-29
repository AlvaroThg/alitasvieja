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

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->sauces = Sauce::all();
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
