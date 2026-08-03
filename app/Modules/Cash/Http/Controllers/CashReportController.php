<?php

namespace App\Modules\Cash\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Modules\Cash\Models\CashMovement;
use App\Modules\Cash\Models\CashSession;
use App\Modules\Cash\Services\CashReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CashReportController extends Controller
{
    protected CashReportService $cashReportService;

    public function __construct(CashReportService $cashReportService)
    {
        $this->cashReportService = $cashReportService;
    }

    /**
     * Aislamiento por sucursal: el Owner consulta la que pida (o todas); el
     * resto queda limitado a la suya, aunque mande otro branch_id.
     */
    protected function scopedBranchId(Request $request, ?int $requested): ?int
    {
        $user = $request->user();

        return $user->isOwner() ? $requested : $user->activeBranchId();
    }

    // MÉTODO NUEVO — Fase 4
    /**
     * GET /admin/reports/cash
     * Historial de sesiones de caja paginado.
     *
     * Parámetros: branch_id?, from (Y-m-d), to (Y-m-d), page?
     * Roles: owner, branch_admin
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => 'nullable|integer|exists:branches,id',
            'from'      => 'required|date_format:Y-m-d',
            'to'        => 'required|date_format:Y-m-d',
        ]);

        $branchId = $this->scopedBranchId($request, $validated['branch_id'] ?? null);
        $from     = Carbon::parse($validated['from']);
        $to       = Carbon::parse($validated['to']);

        $paginator = $this->cashReportService->getSessionHistory($branchId, $from, $to);

        return response()->json([
            'data'         => $paginator->items(),
            'current_page' => $paginator->currentPage(),
            'last_page'    => $paginator->lastPage(),
            'total'        => $paginator->total(),
        ]);
    }

    // MÉTODO NUEVO — Fase 4
    /**
     * GET /admin/reports/cash/{session}
     * Detalle completo de una sesión de caja.
     *
     * Roles: owner, branch_admin
     */
    public function show(CashSession $session): JsonResponse
    {
        // Un admin de sucursal no puede abrir el detalle de una caja ajena.
        $user = auth()->user();
        abort_unless(
            $user->isOwner() || $session->branch_id === $user->activeBranchId(),
            403,
            'Esta caja pertenece a otra sucursal.'
        );

        $data = $this->cashReportService->getSessionDetail($session);

        return response()->json($data);
    }

    // MÉTODO NUEVO — Fase 4
    /**
     * GET /admin/reports/cash/export
     * Exportar historial de sesiones de caja como CSV.
     *
     * Mismos parámetros que index() pero sin paginación.
     * NOTA: esta ruta debe definirse ANTES que /{session} para
     * evitar que Laravel interprete "export" como un ID de sesión.
     *
     * Roles: owner, branch_admin
     */
    public function export(Request $request): Response
    {
        $validated = $request->validate([
            'branch_id' => 'nullable|integer|exists:branches,id',
            'from'      => 'required|date_format:Y-m-d',
            'to'        => 'required|date_format:Y-m-d',
        ]);

        $branchId = $this->scopedBranchId($request, $validated['branch_id'] ?? null);
        $from     = Carbon::parse($validated['from']);
        $to       = Carbon::parse($validated['to']);

        $csv = $this->cashReportService->exportSessionsCsv($branchId, $from, $to);

        $filename = 'cierres_caja_' . $validated['from'] . '_' . $validated['to'] . '.csv';

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * GET /admin/reports/cash/movements/export
     * Reporte PDF de movimientos de caja (ingresos, egresos, caja chica y traspasos),
     * respetando los filtros recibidos por query string.
     */
    public function movementsExport(Request $request)
    {
        $from = $request->query('date_from');
        $to = $request->query('date_to');
        $type = $request->query('type');
        $cashBox = $request->query('cash_box');

        // Aislamiento por sucursal: solo el Owner puede exportar otras sucursales;
        // al resto se le fuerza la suya aunque cambie el branch_id de la URL.
        $user = $request->user();
        $branchId = $user->isOwner()
            ? $request->query('branch_id')
            : $user->activeBranchId();

        $movements = CashMovement::with(['cashSession.branch', 'user'])
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->when($branchId, fn ($q) => $q->whereHas('cashSession', fn ($s) => $s->where('branch_id', $branchId)))
            ->when($type, fn ($q) => $q->where('type', $type))
            ->when($cashBox, fn ($q) => $q->where('cash_box', $cashBox))
            ->latest('id')
            ->get();

        // Cierres de caja del período: el arqueo (esperado vs. contado y su
        // diferencia) vive en la sesión, no en los movimientos.
        $sessions = CashSession::with(['branch', 'closedBy'])
            ->whereNotNull('closed_at')
            ->when($from, fn ($q) => $q->whereDate('closed_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('closed_at', '<=', $to))
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->latest('closed_at')
            ->get();

        $branchName = $branchId ? (Branch::find($branchId)->name ?? 'Todas') : 'Todas';
        $totalIncome = (float) $movements->where('type', 'income')->sum('amount');
        $totalExpense = (float) $movements->where('type', 'expense')->sum('amount');
        $totalDifference = (float) $sessions->sum(fn ($s) => (float) $s->difference);

        // Ingreso neto: ventas de todos los métodos de pago menos gastos reales
        // (los traspasos a Caja Chica no son gasto, solo mueven dinero interno).
        $sales = (float) \App\Modules\Orders\Models\Order::where('status', 'paid')
            ->when($from, fn ($q) => $q->whereDate('closed_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('closed_at', '<=', $to))
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->sum('total');
        $realExpenses = (float) $movements->where('type', 'expense')
            ->where('cash_box', '!=', 'transfer')->sum('amount');

        $pdf = Pdf::loadView('reports.cash-movements-pdf', [
            'movements'       => $movements,
            'sessions'        => $sessions,
            'from'            => $from,
            'to'              => $to,
            'branchName'      => $branchName,
            'totalIncome'     => $totalIncome,
            'totalExpense'    => $totalExpense,
            'totalDifference' => $totalDifference,
            'sales'           => $sales,
            'realExpenses'    => $realExpenses,
            'netIncome'       => $sales - $realExpenses,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('movimientos_caja.pdf');
    }
}
