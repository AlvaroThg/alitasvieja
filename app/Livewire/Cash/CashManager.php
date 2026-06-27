<?php

namespace App\Livewire\Cash;

use Livewire\Component;
use App\Modules\Cash\Services\CashService;
use App\Modules\Cash\Models\CashSession;
use App\Modules\Cash\Models\CashMovement;
use Illuminate\Support\Facades\Auth;

class CashManager extends Component
{
    public $session = null;
    public $opening_amount = '';
    
    public $type = 'income';
    public $amount = '';
    public $concept = '';
    public $reference = '';

    public $petty_amount = '';

    public function mount(CashService $cashService)
    {
        $this->loadSession($cashService);
    }

    public function loadSession(CashService $cashService)
    {
        $branchId = Auth::user()->activeBranchId();
        if ($branchId) {
            $this->session = $cashService->getActiveSession($branchId);
        }
    }

    public function openSession(CashService $cashService)
    {
        $this->validate([
            'opening_amount' => 'required|numeric|min:0'
        ]);

        $branchId = Auth::user()->activeBranchId();
        
        if (!$branchId) {
            $this->addError('opening_amount', 'No tienes una sucursal activa seleccionada. (Si eres Owner, cambia de sucursal en el panel superior o dashboard).');
            return;
        }

        $cashService->openSession($branchId, Auth::id(), (float)$this->opening_amount);
        $this->loadSession($cashService);
        $this->opening_amount = '';
    }

    public function addMovement(CashService $cashService)
    {
        $this->validate([
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0.01',
            'concept' => 'required|string|max:255',
            'reference' => 'nullable|string|max:100',
        ]);

        if ($this->session) {
            if ($this->type === 'expense') {
                // Los egresos se pagan desde la Caja Chica (con traspaso automático si no alcanza).
                $cashService->registerPettyExpense($this->session, Auth::id(), (float) $this->amount, $this->concept, $this->reference ?: null);
            } else {
                $cashService->addMovement($this->session, Auth::id(), [
                    'type' => 'income',
                    'amount' => $this->amount,
                    'concept' => $this->concept,
                    'reference' => $this->reference,
                ]);
            }

            $this->amount = '';
            $this->concept = '';
            $this->reference = '';
            $this->loadSession($cashService);
        }
    }

    public function loadPettyCash(CashService $cashService)
    {
        $this->validate(['petty_amount' => 'required|numeric|min:0.01']);

        if ($this->session) {
            $cashService->loadPettyCash($this->session, Auth::id(), (float) $this->petty_amount);
            $this->petty_amount = '';
            $this->loadSession($cashService);
        }
    }

    public function render()
    {
        $movements = $this->session ? $this->session->movements()->latest()->get() : [];

        $pettyBalance = 0.0;
        $branchId = Auth::user()->activeBranchId();
        if ($branchId) {
            $pettyBalance = (float) (\App\Models\Branch::find($branchId)->petty_cash_balance ?? 0);
        }

        return view('livewire.cash.cash-manager', [
            'movements' => $movements,
            'pettyBalance' => $pettyBalance,
        ]);
    }
}
