<?php

namespace App\Livewire\Pos;

use Livewire\Component;
use App\Models\Table;

class TableGrid extends Component
{
    public $branchId = 1;

    // Modal de acciones sobre mesa existente
    public $selectedTableForAction = null;
    public $showActionModal = false;

    // Modal de checkout
    public $showCheckoutModal = false;
    public $checkoutPaymentMethod = 'cash';
    public $checkoutOrderTotal = 0;

    // Modal de crear mesa
    public $showCreateTableModal = false;
    public $newTableName = '';

    // Modal de confirmar eliminación
    public $showDeleteTableModal = false;
    public $tableToDeleteId = null;
    public $deleteErrorMessage = '';

    // ─── ACCIONES SOBRE MESA ──────────────────────────────────

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
        if ($this->selectedTableForAction) {
            $this->dispatch('table-selected', id: $this->selectedTableForAction->id);
        }
    }

    /**
     * Pedido para llevar / delivery: sin mesa. El OrderBuilder lo interpreta
     * como 'takeaway' (id null) y su ticket muestra "Cocina" en vez de mesa.
     */
    public function createTakeawayOrder()
    {
        $this->dispatch('table-selected', id: null);
    }

    // ─── CREAR MESA ──────────────────────────────────────────

    public function openCreateTableModal()
    {
        $this->newTableName = '';
        $this->showCreateTableModal = true;
    }

    public function createTable()
    {
        $this->validate([
            'newTableName' => 'required|string|max:50',
        ], [
            'newTableName.required' => 'El nombre o número de la mesa es obligatorio.',
        ]);

        $branchId = auth()->user()->activeBranchId() ?? 1;

        Table::create([
            'name'      => $this->newTableName,
            'status'    => 'available',
            'branch_id' => $branchId,
        ]);

        $this->showCreateTableModal = false;
        $this->newTableName = '';
    }

    // ─── ELIMINAR MESA ───────────────────────────────────────

    public function confirmDeleteTable($tableId)
    {
        $table = Table::find($tableId);

        if (!$table) return;

        $this->deleteErrorMessage = '';

        // Regla 1: No se puede eliminar si está reservada
        if ($table->status === 'reserved') {
            $this->deleteErrorMessage = 'No se puede eliminar la mesa "' . $table->name . '" porque está reservada.';
            $this->showActionModal = false;
            $this->showDeleteTableModal = true;
            $this->tableToDeleteId = null; // No permitir confirmar
            return;
        }

        // Regla 2: No se puede eliminar si tiene órdenes activas
        if ($table->hasActiveOrders()) {
            $this->deleteErrorMessage = 'No se puede eliminar la mesa "' . $table->name . '" porque tiene un pedido activo.';
            $this->showActionModal = false;
            $this->showDeleteTableModal = true;
            $this->tableToDeleteId = null;
            return;
        }

        // Todo ok, pedir confirmación
        $this->tableToDeleteId = $tableId;
        $this->showActionModal = false;
        $this->showDeleteTableModal = true;
    }

    public function deleteTable()
    {
        if (!$this->tableToDeleteId) return;

        $table = Table::find($this->tableToDeleteId);
        if ($table) {
            $table->delete();
        }

        $this->showDeleteTableModal = false;
        $this->tableToDeleteId = null;
        $this->selectedTableForAction = null;
    }

    // ─── RENDER ──────────────────────────────────────────────

    public function render()
    {
        $branchId = auth()->user()->activeBranchId() ?? 1;
        $tables = Table::where('branch_id', $branchId)->get();
        return view('livewire.pos.table-grid', compact('tables'));
    }
}