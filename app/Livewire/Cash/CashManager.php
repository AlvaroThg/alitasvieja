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
    public $closing_qr = '';
    public $closing_notes = '';
    public $showSurplusConfirm = false;
    public $surplusAmount = 0;

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
        $this->closing_qr = '';
        $this->closing_notes = '';
        $this->showCloseModal = true;
        $this->showSurplusConfirm = false;
        $this->surplusAmount = 0;
    }

    public function closeSession(CashService $cashService)
    {
        $this->validate([
            'closing_amount' => 'required|numeric|min:0',
            'closing_qr' => 'required|numeric|min:0',
            'closing_notes' => 'nullable|string|max:255',
        ], [
            'closing_amount.required' => 'Debe ingresar el monto contado en efectivo.',
            'closing_amount.numeric' => 'El monto en efectivo debe ser un número.',
            'closing_amount.min' => 'El monto en efectivo no puede ser negativo.',
            'closing_qr.required' => 'Debe ingresar el monto verificado en QR.',
            'closing_qr.numeric' => 'El monto QR debe ser un número.',
            'closing_qr.min' => 'El monto QR no puede ser negativo.',
        ]);

        if (!$this->session) return;

        // Verificar si hay sobrante en efectivo o QR
        $expectedCash = $this->session->calculateExpected();
        $expectedQr = $this->session->getTotalByPaymentMethod('qr');
        $countedCash = (float) $this->closing_amount;
        $countedQr = (float) $this->closing_qr;

        $cashSurplus = max(0.0, $countedCash - $expectedCash);
        $qrSurplus = max(0.0, $countedQr - $expectedQr);
        $totalSurplus = round($cashSurplus + $qrSurplus, 2);

        if ($totalSurplus > 0.01 && !$this->showSurplusConfirm) {
            $this->surplusAmount = $totalSurplus;
            $this->showSurplusConfirm = true;
            return;
        }

        $this->doCloseSession($cashService);
    }

    public function confirmCloseWithSurplus(CashService $cashService)
    {
        $this->doCloseSession($cashService);
    }

    public function cancelSurplusConfirm()
    {
        $this->showSurplusConfirm = false;
        $this->surplusAmount = 0;
    }

    private function doCloseSession(CashService $cashService)
    {
        try {
            $expectedCash = $this->session->calculateExpected();
            $expectedQr = $this->session->getTotalByPaymentMethod('qr');
            $countedCash = (float) $this->closing_amount;
            $countedQr = (float) $this->closing_qr;

            $qrNote = "QR Verificado: Bs. " . number_format($countedQr, 2) . " (Esperado: Bs. " . number_format($expectedQr, 2) . ")";
            $finalNotes = trim(($this->closing_notes ? $this->closing_notes . " | " : "") . $qrNote);

            $closed = $cashService->closeSession(
                $this->session,
                Auth::id(),
                $countedCash,
                $finalNotes
            );

            $this->showCloseModal = false;
            $this->showSurplusConfirm = false;
            $this->surplusAmount = 0;
            $this->session = null;

            $resumen = 'Caja cerrada. '
                . 'Efectivo: Bs. ' . number_format($countedCash, 2) . ' (Esperado: Bs. ' . number_format($expectedCash, 2) . ')'
                . ' | QR: Bs. ' . number_format($countedQr, 2) . ' (Esperado: Bs. ' . number_format($expectedQr, 2) . ')';

            session()->flash('message', $resumen);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->addError('closing_amount', collect($e->errors())->flatten()->first());
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
