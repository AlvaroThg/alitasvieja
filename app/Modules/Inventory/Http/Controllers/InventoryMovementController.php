<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Modules\Inventory\Models\InventoryMovement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InventoryMovementController extends Controller
{
    /**
     * Genera el reporte PDF del historial de movimientos de inventario,
     * respetando los filtros (fechas, sucursal, tipo) recibidos por query string.
     */
    public function export(Request $request)
    {
        $from = $request->query('date_from');
        $to = $request->query('date_to');
        $type = $request->query('type');

        // Aislamiento por sucursal: solo el Owner puede exportar otras sucursales;
        // al resto se le fuerza la suya aunque cambie el branch_id de la URL.
        $user = $request->user();
        $branchId = $user->isOwner()
            ? $request->query('branch_id')
            : $user->activeBranchId();

        $movements = InventoryMovement::with(['productVariant.product', 'branch', 'user'])
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($type, fn ($q) => $q->where('type', $type))
            ->latest('id')
            ->get();

        $branchName = $branchId ? (Branch::find($branchId)->name ?? 'Todas') : 'Todas';

        $pdf = Pdf::loadView('reports.inventory-movements-pdf', [
            'movements'  => $movements,
            'from'       => $from,
            'to'         => $to,
            'branchName' => $branchName,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('movimientos_inventario.pdf');
    }
}
