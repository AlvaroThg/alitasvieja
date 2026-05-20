<?php

namespace App\Modules\Cash\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Cash\Models\CashSession;
use App\Modules\Cash\Services\CashReportService;
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

        $branchId = $validated['branch_id'] ?? null;
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

        $branchId = $validated['branch_id'] ?? null;
        $from     = Carbon::parse($validated['from']);
        $to       = Carbon::parse($validated['to']);

        $csv = $this->cashReportService->exportSessionsCsv($branchId, $from, $to);

        $filename = 'cierres_caja_' . $validated['from'] . '_' . $validated['to'] . '.csv';

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
