<?php

namespace App\Livewire\Admin;

use App\Models\Branch;
use App\Modules\Cash\Models\CashMovement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class CashMovements extends Component
{
    use WithPagination;

    public string $dateFrom = '';
    public string $dateTo = '';
    public $branchId = '';
    public string $type = '';     // income | expense
    public string $cashBox = '';  // sales | petty | transfer

    public function mount(): void
    {
        $this->dateFrom = Carbon::now()->startOfMonth()->toDateString();
        $this->dateTo = Carbon::now()->toDateString();
    }

    public function updating($name): void
    {
        if (in_array($name, ['dateFrom', 'dateTo', 'branchId', 'type', 'cashBox'])) {
            $this->resetPage();
        }
    }

    /**
     * Sucursal por la que se filtra. El Owner ve todas (o la que elija); el
     * resto queda restringido a la suya aunque manipule el filtro.
     */
    protected function scopedBranchId(): ?int
    {
        $user = Auth::user();

        return $user->isOwner()
            ? ($this->branchId ? (int) $this->branchId : null)
            : $user->activeBranchId();
    }

    protected function baseQuery()
    {
        $branchId = $this->scopedBranchId();

        return CashMovement::with(['cashSession.branch', 'user'])
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->when($branchId, fn ($q) => $q->whereHas('cashSession', fn ($s) => $s->where('branch_id', $branchId)))
            ->when($this->type, fn ($q) => $q->where('type', $this->type))
            ->when($this->cashBox, fn ($q) => $q->where('cash_box', $this->cashBox));
    }

    public function render()
    {
        $movements = $this->baseQuery()->latest('id')->paginate(20);

        // Totales del filtro actual (sobre todo el rango, no solo la página)
        $totals = [
            'income'  => (float) $this->baseQuery()->where('type', 'income')->sum('amount'),
            'expense' => (float) $this->baseQuery()->where('type', 'expense')->sum('amount'),
        ];

        $isOwner = Auth::user()->isOwner();

        return view('livewire.admin.cash-movements', [
            'movements' => $movements,
            'branches' => $isOwner
                ? Branch::active()->get()
                : Branch::where('id', Auth::user()->activeBranchId())->get(),
            'totals' => $totals,
            'isOwner' => $isOwner,
        ]);
    }
}
