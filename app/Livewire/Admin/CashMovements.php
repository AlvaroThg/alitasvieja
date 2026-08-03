<?php

namespace App\Livewire\Admin;

use App\Models\Branch;
use App\Modules\Cash\Models\CashMovement;
use App\Modules\Cash\Models\CashSession;
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

    /**
     * Cierres de caja del período: el arqueo vive en la sesión (esperado vs.
     * contado), no en los movimientos, así que se consulta aparte.
     */
    protected function closedSessionsQuery()
    {
        $branchId = $this->scopedBranchId();

        return CashSession::with(['branch', 'closedBy'])
            ->whereNotNull('closed_at')
            ->when($this->dateFrom, fn ($q) => $q->whereDate('closed_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('closed_at', '<=', $this->dateTo))
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));
    }

    public function render()
    {
        $movements = $this->baseQuery()->latest('id')->paginate(20);
        $sessions = $this->closedSessionsQuery()->latest('closed_at')->get();

        // Totales del filtro actual (sobre todo el rango, no solo la página)
        $totals = [
            'income'  => (float) $this->baseQuery()->where('type', 'income')->sum('amount'),
            'expense' => (float) $this->baseQuery()->where('type', 'expense')->sum('amount'),
            // Sobrantes/faltantes detectados al cerrar caja (negativo = faltante).
            'difference' => (float) $sessions->sum(fn ($s) => (float) $s->difference),
        ];

        $isOwner = Auth::user()->isOwner();

        return view('livewire.admin.cash-movements', [
            'movements' => $movements,
            'sessions' => $sessions,
            'branches' => $isOwner
                ? Branch::active()->get()
                : Branch::where('id', Auth::user()->activeBranchId())->get(),
            'totals' => $totals,
            'isOwner' => $isOwner,
        ]);
    }
}
