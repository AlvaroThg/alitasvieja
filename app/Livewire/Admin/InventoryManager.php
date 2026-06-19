<?php

namespace App\Livewire\Admin;

use App\Models\Branch;
use App\Modules\Inventory\Models\Inventory;
use App\Modules\Inventory\Models\InventoryMovement;
use App\Modules\Menu\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class InventoryManager extends Component
{
    use WithPagination;

    // Filtros
    public $search = '';
    public $branchId = '';

    // Modal de Ajuste de Stock
    public $showAdjustModal = false;
    public $inventoryIdToAdjust = null;
    public $adjustmentQuantity = 0;
    public $adjustmentType = 'in';
    public $adjustmentReason = '';

    // Modal de Crear/Editar registro
    public $showCreateModal = false;
    public $isEdit = false;
    public $editInventoryId = null;
    public $formBranchId = '';
    public $formVariantId = '';
    public $formStockQuantity = 0;
    public $formMinimumAlert = 0;

    // Modal de Confirmar Eliminación
    public $showDeleteModal = false;
    public $deleteInventoryId = null;
    public $deleteItemName = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'branchId' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingBranchId()
    {
        $this->resetPage();
    }

    // ─── CREAR ─────────────────────────────────────────────────

    public function openCreateModal()
    {
        $this->resetValidation();
        $this->isEdit = false;
        $this->editInventoryId = null;
        $this->formBranchId = '';
        $this->formVariantId = '';
        $this->formStockQuantity = 0;
        $this->formMinimumAlert = 0;
        $this->showCreateModal = true;
    }

    public function saveCreate()
    {
        $this->validate([
            'formBranchId' => 'required|exists:branches,id',
            'formVariantId' => 'required|exists:product_variants,id',
            'formStockQuantity' => 'required|integer|min:0',
            'formMinimumAlert' => 'required|integer|min:0',
        ]);

        if ($this->isEdit) {
            $inv = Inventory::find($this->editInventoryId);
            if ($inv) {
                $inv->update([
                    'minimum_alert' => $this->formMinimumAlert,
                ]);
                session()->flash('message', 'Registro de inventario actualizado.');
            }
        } else {
            // Verificar que no exista duplicado
            $exists = Inventory::where('product_variant_id', $this->formVariantId)
                ->where('branch_id', $this->formBranchId)
                ->first();

            if ($exists) {
                $this->addError('formVariantId', 'Ya existe un registro de inventario para esta variante en esta sucursal.');
                return;
            }

            Inventory::create([
                'product_variant_id' => $this->formVariantId,
                'branch_id' => $this->formBranchId,
                'stock_quantity' => $this->formStockQuantity,
                'minimum_alert' => $this->formMinimumAlert,
            ]);
            session()->flash('message', 'Registro de inventario creado.');
        }

        $this->showCreateModal = false;
    }

    // ─── EDITAR ────────────────────────────────────────────────

    public function openEditModal($inventoryId)
    {
        $this->resetValidation();
        $inv = Inventory::find($inventoryId);
        if (!$inv) return;

        $this->isEdit = true;
        $this->editInventoryId = $inv->id;
        $this->formBranchId = $inv->branch_id;
        $this->formVariantId = $inv->product_variant_id;
        $this->formStockQuantity = $inv->stock_quantity;
        $this->formMinimumAlert = $inv->minimum_alert;
        $this->showCreateModal = true;
    }

    // ─── ELIMINAR ──────────────────────────────────────────────

    public function confirmDelete($inventoryId)
    {
        $inv = Inventory::with('productVariant.product')->find($inventoryId);
        if (!$inv) return;

        $this->deleteInventoryId = $inventoryId;
        $this->deleteItemName = ($inv->productVariant->product->name ?? '') . ' — ' . ($inv->productVariant->name ?? '');
        $this->showDeleteModal = true;
    }

    public function deleteInventory()
    {
        $inv = Inventory::find($this->deleteInventoryId);
        if ($inv) {
            // Eliminar movimientos relacionados
            InventoryMovement::where('product_variant_id', $inv->product_variant_id)
                ->where('branch_id', $inv->branch_id)
                ->delete();
            $inv->delete();
            session()->flash('message', 'Registro de inventario eliminado.');
        }
        $this->showDeleteModal = false;
        $this->deleteInventoryId = null;
    }

    // ─── AJUSTAR STOCK ────────────────────────────────────────

    public function openAdjustModal($inventoryId)
    {
        $this->resetValidation();
        $this->inventoryIdToAdjust = $inventoryId;
        $this->adjustmentQuantity = 0;
        $this->adjustmentType = 'in';
        $this->adjustmentReason = '';
        $this->showAdjustModal = true;
    }

    public function closeAdjustModal()
    {
        $this->showAdjustModal = false;
        $this->inventoryIdToAdjust = null;
    }

    public function saveAdjustment()
    {
        $this->validate([
            'adjustmentQuantity' => 'required|numeric|min:1',
            'adjustmentType' => 'required|in:in,out,adjustment',
            'adjustmentReason' => 'required|string|max:255',
        ]);

        $inventory = Inventory::findOrFail($this->inventoryIdToAdjust);
        $stockBefore = $inventory->stock_quantity;

        if ($this->adjustmentType === 'out') {
            $stockAfter = $stockBefore - $this->adjustmentQuantity;
        } else {
            $stockAfter = $stockBefore + $this->adjustmentQuantity;
        }

        InventoryMovement::create([
            'product_variant_id' => $inventory->product_variant_id,
            'branch_id' => $inventory->branch_id,
            'user_id' => Auth::id(),
            'type' => $this->adjustmentType,
            'quantity' => $this->adjustmentQuantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'reason' => $this->adjustmentReason,
        ]);

        $inventory->update([
            'stock_quantity' => $stockAfter
        ]);

        $this->closeAdjustModal();
        session()->flash('message', 'Stock ajustado correctamente.');
    }

    // ─── RENDER ───────────────────────────────────────────────

    public function render()
    {
        $branches = Branch::active()->get();
        // Solo variantes de productos que NO son alitas (las alitas usan su propio
        // control de stock por kilos). El inventario aquí es para helados, bebidas, etc.
        $variants = ProductVariant::with('product')
            ->whereHas('product', fn($q) => $q->where('is_wings', false))
            ->get();

        $inventoryList = Inventory::with(['productVariant.product', 'branch'])
            ->when($this->branchId, function ($query) {
                $query->where('branch_id', $this->branchId);
            })
            ->when($this->search, function ($query) {
                $query->whereHas('productVariant.product', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->paginate(15);

        return view('livewire.admin.inventory-manager', [
            'branches' => $branches,
            'inventoryList' => $inventoryList,
            'variants' => $variants,
        ]);
    }
}