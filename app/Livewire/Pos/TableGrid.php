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
            if ($status === 'available') {
                // Cobrar la orden abierta si existe
                $openOrder = \App\Modules\Orders\Models\Order::where('table_id', $this->selectedTableForAction->id)
                    ->where('status', 'open')
                    ->first();

                if ($openOrder && $openOrder->total > 0) {
                    $checkoutService = app(\App\Modules\Orders\Services\CheckoutService::class);
                    $checkoutService->processPayment($openOrder, [
                        ['method' => 'cash', 'amount' => $openOrder->total]
                    ]);
                } elseif ($openOrder) {
                    // Si el total es 0, simplemente cerrarla (o marcar como pagada con efectivo 0 si el servicio lo permite)
                    $openOrder->update([
                        'status' => 'paid',
                        'closed_at' => now(),
                        'payment_method' => 'cash'
                    ]);
                }
            }

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