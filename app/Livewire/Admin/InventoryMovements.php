<?php

namespace App\Livewire\Admin;

use App\Models\Branch;
use App\Modules\Inventory\Models\InventoryMovement;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class InventoryMovements extends Component
{
    use WithPagination;

    public string $dateFrom = '';
    public string $dateTo = '';
    public $branchId = '';
    public string $type = '';
    public string $search = '';

    public function mount(): void
    {
        $this->dateFrom = Carbon::now()->startOfMonth()->toDateString();
        $this->dateTo = Carbon::now()->toDateString();
    }

    public function updating($name): void
    {
        if (in_array($name, ['dateFrom', 'dateTo', 'branchId', 'type', 'search'])) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $movements = InventoryMovement::with(['productVariant.product', 'branch', 'user'])
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->when($this->branchId, fn ($q) => $q->where('branch_id', $this->branchId))
            ->when($this->type, fn ($q) => $q->where('type', $this->type))
            ->when($this->search, fn ($q) => $q->whereHas('productVariant.product', fn ($p) => $p->where('name', 'like', '%' . $this->search . '%')))
            ->latest('id')
            ->paginate(20);

        return view('livewire.admin.inventory-movements', [
            'movements' => $movements,
            'branches' => Branch::active()->get(),
        ]);
    }
}
