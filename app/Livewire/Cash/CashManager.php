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

    // Cierre de caja
    public $showCloseModal = false;
    public $closing_amount = '';
    public $closing_notes = '';

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
        ], [
            'opening_amount.required' => 'Debe ingresar el monto inicial de la caja.',
            'opening_amount.numeric' => 'El monto inicial debe ser un número.',
            'opening_amount.min' => 'El monto inicial no puede ser negativo.',
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
        ], [
            'amount.required' => 'Debe ingresar el monto del movimiento.',
            'amount.numeric' => 'El monto debe ser un número.',
            'amount.min' => 'El monto debe ser mayor a 0. No se permiten montos negativos.',
            'concept.required' => 'Debe ingresar el concepto del movimiento.',
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
        $this->validate([
            'petty_amount' => 'required|numeric|min:0.01',
        ], [
            'petty_amount.required' => 'Debe ingresar el monto a cargar.',
            'petty_amount.numeric' => 'El monto debe ser un número.',
            'petty_amount.min' => 'El monto debe ser mayor a 0. No se permiten montos negativos.',
        ]);

        if ($this->session) {
            $cashService->loadPettyCash($this->session, Auth::id(), (float) $this->petty_amount);
            $this->petty_amount = '';
            $this->loadSession($cashService);
        }
    }

    public function openCloseModal()
    {
        $this->resetValidation();
        $this->closing_amount = '';
        $this->closing_notes = '';
        $this->showCloseModal = true;
    }

    public function closeSession(CashService $cashService)
    {
        $this->validate([
            'closing_amount' => 'required|numeric|min:0',
            'closing_notes' => 'nullable|string|max:255',
        ], [
            'closing_amount.required' => 'Debe ingresar el monto contado en caja.',
            'closing_amount.numeric' => 'El monto debe ser un número.',
            'closing_amount.min' => 'El monto no puede ser negativo.',
        ]);

        if (!$this->session) return;

        $closed = $cashService->closeSession(
            $this->session,
            Auth::id(),
            (float) $this->closing_amount,
            $this->closing_notes ?: null
        );

        $this->showCloseModal = false;
        $this->session = null;

        $diff = (float) $closed->difference;
        $resumen = 'Caja cerrada. Esperado: Bs. ' . number_format((float) $closed->expected_amount, 2)
            . ' | Contado: Bs. ' . number_format((float) $closed->closing_amount, 2)
            . ' | Diferencia: Bs. ' . number_format($diff, 2)
            . ($diff == 0.0 ? ' (cuadre exacto)' : ($diff > 0 ? ' (sobrante)' : ' (faltante)'));

        session()->flash('message', $resumen);
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
