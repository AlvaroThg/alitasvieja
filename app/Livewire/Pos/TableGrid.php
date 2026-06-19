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

    public $showCheckoutModal = false;
    public $checkoutPaymentMethod = 'cash';
    public $checkoutOrderTotal = 0;

    public function changeStatus($status)
    {
        if ($this->selectedTableForAction) {
            $this->selectedTableForAction->update(['status' => $status]);
            $this->showActionModal = false;
        }
    }

    public function openCheckout()
    {
        $openOrder = \App\Modules\Orders\Models\Order::where('table_id', $this->selectedTableForAction->id)
            ->where('status', 'open')
            ->first();
            
        if ($openOrder) {
            $this->checkoutOrderTotal = $openOrder->total;
            $this->showActionModal = false;
            $this->showCheckoutModal = true;
        } else {
            // Liberar la mesa si no hay orden
            $this->changeStatus('available');
        }
    }

    public function processCheckout()
    {
        $openOrder = \App\Modules\Orders\Models\Order::where('table_id', $this->selectedTableForAction->id)
            ->where('status', 'open')
            ->first();

        if ($openOrder && $openOrder->total > 0) {
            $checkoutService = app(\App\Modules\Orders\Services\CheckoutService::class);
            $checkoutService->processPayment($openOrder, [
                ['method' => $this->checkoutPaymentMethod, 'amount' => $openOrder->total]
            ]);
        } elseif ($openOrder) {
            $openOrder->update([
                'status' => 'paid',
                'closed_at' => now(),
                'payment_method' => $this->checkoutPaymentMethod
            ]);
        }
        
        $this->selectedTableForAction->update(['status' => 'available']);
        $this->showCheckoutModal = false;
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