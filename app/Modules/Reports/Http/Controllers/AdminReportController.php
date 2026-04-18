<?php

namespace App\Modules\Reports\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Reports\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminReportController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * GET /admin/dashboard
     * Dashboard con KPIs del día actual.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $today = Carbon::today();

        // Resumen global (todas las sucursales)
        $global = $this->reportService->getSalesSummary(null, $today, $today);

        // Resumen por sucursal
        $branches = \App\Models\Branch::active()->get();
        $perBranch = $branches->map(function ($branch) use ($today) {
            $summary = $this->reportService->getSalesSummary($branch->id, $today, $today);
            return array_merge(['branch_id' => $branch->id, 'branch_name' => $branch->name], $summary);
        });

        return response()->json([
            'date'       => $today->toDateString(),
            'global'     => $global,
            'per_branch' => $perBranch,
        ]);
    }

    /**
     * GET /admin/orders
     * Listado histórico con filtros por fecha, sucursal y búsqueda.
     */
    public function orders(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => 'nullable|integer|exists:branches,id',
            'from'      => 'nullable|date_format:Y-m-d',
            'to'        => 'nullable|date_format:Y-m-d',
            'search'    => 'nullable|string|max:50',
        ]);

        $branchId = $validated['branch_id'] ?? null;

        // Si hay búsqueda por order_number/daily_number
        if (!empty($validated['search'])) {
            $results = $this->reportService->searchByOrderNumber(
                $validated['search'],
                $branchId
            );

            return response()->json(['data' => $results]);
        }

        // Si hay rango de fechas, usar ese rango
        $from = isset($validated['from']) ? Carbon::parse($validated['from']) : Carbon::today();
        $to   = isset($validated['to'])   ? Carbon::parse($validated['to'])   : Carbon::today();

        $orders  = $this->reportService->getOrdersByDateRange($branchId, $from, $to);
        $summary = $this->reportService->getSalesSummary($branchId, $from, $to);
        $topProducts = $this->reportService->getTopProducts($branchId, $from, $to);

        return response()->json([
            'summary'      => $summary,
            'top_products' => $topProducts,
            'orders'       => $orders,
        ]);
    }
}
