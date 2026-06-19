<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class UserManager extends Component
{
    public $users;
    public $branches;
    
    public $showModal = false;
    public $isEdit = false;
    public $userId;

    // Form fields
    public $name;
    public $email;
    public $password;
    public $role = 'cashier';
    public $branch_id;

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->users = User::with('branch')->get();
        $this->branches = Branch::all();
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
        
        $user = User::find($id);
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->branch_id = $user->branch_id;
        // Password no se carga por seguridad. Solo se cambia si se escribe algo.

        $this->showModal = true;
    }

    public function save()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . ($this->userId ?? 'NULL'),
            'role' => 'required|in:owner,cashier',
            'branch_id' => 'required|exists:branches,id',
        ];

        if (!$this->isEdit) {
            $rules['password'] = 'required|min:6';
        } else {
            $rules['password'] = 'nullable|min:6';
        }

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'branch_id' => $this->branch_id,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->isEdit) {
            User::find($this->userId)->update($data);
        } else {
            User::create($data);
        }

        $this->showModal = false;
        $this->loadData();
    }

    public function resetFields()
    {
        $this->userId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->role = 'cashier';
        $this->branch_id = '';
    }

    public function render()
    {
        return view('livewire.admin.user-manager');
    }
}
