<?php

namespace App\Livewire\Pos;

use Livewire\Component;
use App\Models\Table; 

class TableGrid extends Component
{
    public $branchId = 1; 

    public $selectedTableForAction = null;
    public $showActionModal = false;

    public function manageTable($id)
    {
        $this->selectedTableForAction = Table::find($id);
        if ($this->selectedTableForAction) {
            $this->showActionModal = true;
        }
    }

    public function changeStatus($status)
    {
        if ($this->selectedTableForAction) {
            $this->selectedTableForAction->update(['status' => $status]);
            $this->showActionModal = false;
        }
    }

    public function createOrder()
    {
        $this->showActionModal = false;
        if($this->selectedTableForAction) {
             $this->dispatch('table-selected', id: $this->selectedTableForAction->id);
        }
    }

    public function render()
    {
        $branchId = auth()->user()->activeBranchId() ?? 1;
        $tables = Table::where('branch_id', $branchId)->get();
        return view('livewire.pos.table-grid', compact('tables'));
    }
}