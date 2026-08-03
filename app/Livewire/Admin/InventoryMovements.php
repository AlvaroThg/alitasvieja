<?php

namespace App\Livewire\Admin;

use App\Models\Branch;
use App\Modules\Inventory\Models\InventoryMovement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
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

    /**
     * El Owner ve todas las sucursales; el resto solo la suya, aunque
     * manipule el filtro.
     */
    protected function scopedBranchId(): ?int
    {
        $user = Auth::user();

        return $user->isOwner()
            ? ($this->branchId ? (int) $this->branchId : null)
            : $user->activeBranchId();
    }

    public function render()
    {
        $branchId = $this->scopedBranchId();

        $movements = InventoryMovement::with(['productVariant.product', 'branch', 'user'])
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($this->type, fn ($q) => $q->where('type', $this->type))
            ->when($this->search, fn ($q) => $q->whereHas('productVariant.product', fn ($p) => $p->where('name', 'like', '%' . $this->search . '%')))
            ->latest('id')
            ->paginate(20);

        $isOwner = Auth::user()->isOwner();

        return view('livewire.admin.inventory-movements', [
            'movements' => $movements,
            'branches' => $isOwner
                ? Branch::active()->get()
                : Branch::where('id', Auth::user()->activeBranchId())->get(),
            'isOwner' => $isOwner,
        ]);
    }
}
