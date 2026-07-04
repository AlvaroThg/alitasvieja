<?php

namespace App\Livewire\Admin;

use App\Modules\Menu\Models\Category;
use Livewire\Component;

class CategoryManager extends Component
{
    public $categories;
    public $showModal = false;
    public $isEdit = false;
    public $categoryId;

    // Form fields
    public $name;
    public $description;
    public $is_active = true;

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->categories = Category::all();
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
        
        $category = Category::find($id);
        $this->categoryId = $category->id;
        $this->name = $category->name;
        $this->description = $category->description;
        $this->is_active = (bool)$category->is_active;

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
        ]);

        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ];

        if ($this->isEdit) {
            Category::find($this->categoryId)->update($data);
        } else {
            Category::create($data);
        }

        $this->showModal = false;
        $this->loadData();
    }

    public function resetFields()
    {
        $this->categoryId = null;
        $this->name = '';
        $this->description = '';
        $this->is_active = true;
    }

    public function render()
    {
        return view('livewire.admin.category-manager');
    }
}
