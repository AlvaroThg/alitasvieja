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
     * Dashboard con KPIs — datos cargados por Livewire (AdminDashboard).
     */
    public function dashboard(Request $request)
    {
        return view('admin.dashboard');
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

    // ─────────────────────────────────────────────────────────────────────────────
    // MÉTODOS NUEVOS — Fase 4: Dashboard y Reportes
    // ─────────────────────────────────────────────────────────────────────────────

    // MÉTODO NUEVO — Fase 4
    /**
     * GET /admin/dashboard/data
     * KPIs del dashboard con soporte para períodos y comparación.
     *
     * Parámetros: period (today|week|month|custom), from?, to?, branch_id?
     * Si period='custom', from y to son requeridos en formato Y-m-d.
     * Roles: owner
     */
    public function dashboardData(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'period'    => 'sometimes|string|in:today,week,month,custom',
            'from'      => 'required_if:period,custom|nullable|date_format:Y-m-d',
            'to'        => 'required_if:period,custom|nullable|date_format:Y-m-d',
            'branch_id' => 'nullable|integer|exists:branches,id',
        ]);

        $period   = $validated['period'] ?? 'today';
        $branchId = $validated['branch_id'] ?? null;
        $from     = isset($validated['from']) ? Carbon::parse($validated['from']) : null;
        $to       = isset($validated['to'])   ? Carbon::parse($validated['to'])   : null;

        $data = $this->reportService->getDashboardSummary($branchId, $period, $from, $to);

        return response()->json($data);
    }

    // MÉTODO NUEVO — Fase 4
    /**
     * GET /admin/reports/sales
     * Serie temporal de ventas para Chart.js.
     *
     * Parámetros: branch_id?, from (Y-m-d), to (Y-m-d), group_by (day|week|month)
     * Retorna: { labels: [...], datasets: [{data: [revenue]}, {data: [orders]}] }
     * Roles: owner
     */
    public function salesByPeriod(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => 'nullable|integer|exists:branches,id',
            'from'      => 'required|date_format:Y-m-d',
            'to'        => 'required|date_format:Y-m-d',
            'group_by'  => 'required|string|in:day,week,month',
        ]);

        $branchId = $validated['branch_id'] ?? null;
        $from     = Carbon::parse($validated['from']);
        $to       = Carbon::parse($validated['to']);
        $groupBy  = $validated['group_by'];

        $series = $this->reportService->getSalesByPeriod($branchId, $from, $to, $groupBy);

        // Formatear para Chart.js
        return response()->json([
            'labels'   => $series->pluck('period')->toArray(),
            'datasets' => [
                [
                    'label' => 'Ingresos (Bs)',
                    'data'  => $series->pluck('revenue')->toArray(),
                ],
                [
                    'label' => 'Pedidos',
                    'data'  => $series->pluck('orders')->toArray(),
                ],
            ],
        ]);
    }

    // MÉTODO NUEVO — Fase 4
    /**
     * GET /admin/reports/payments
     * Desglose de métodos de pago.
     *
     * Parámetros: branch_id?, from (Y-m-d), to (Y-m-d)
     * Roles: owner, branch_admin
     */
    public function paymentMethods(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => 'nullable|integer|exists:branches,id',
            'from'      => 'required|date_format:Y-m-d',
            'to'        => 'required|date_format:Y-m-d',
        ]);

        $branchId = $validated['branch_id'] ?? null;
        $from     = Carbon::parse($validated['from']);
        $to       = Carbon::parse($validated['to']);

        $data = $this->reportService->getPaymentMethodBreakdown($branchId, $from, $to);

        return response()->json($data);
    }

    // MÉTODO NUEVO — Fase 4
    /**
     * GET /admin/reports/wings
     * Estadísticas de alitas.
     *
     * Parámetros: branch_id?, from (Y-m-d), to (Y-m-d)
     * Roles: owner, branch_admin
     */
    public function wingStats(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => 'nullable|integer|exists:branches,id',
            'from'      => 'required|date_format:Y-m-d',
            'to'        => 'required|date_format:Y-m-d',
        ]);

        $branchId = $validated['branch_id'] ?? null;
        $from     = Carbon::parse($validated['from']);
        $to       = Carbon::parse($validated['to']);

        $data = $this->reportService->getWingStatistics($branchId, $from, $to);

        return response()->json($data);
    }

    // MÉTODO NUEVO — Fase 4
    /**
     * GET /admin/reports/promotions
     * Estadísticas de promociones.
     *
     * Parámetros: branch_id?, from (Y-m-d), to (Y-m-d)
     * Roles: owner
     */
    public function promotionStats(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => 'nullable|integer|exists:branches,id',
            'from'      => 'required|date_format:Y-m-d',
            'to'        => 'required|date_format:Y-m-d',
        ]);

        $branchId = $validated['branch_id'] ?? null;
        $from     = Carbon::parse($validated['from']);
        $to       = Carbon::parse($validated['to']);

        $data = $this->reportService->getPromotionStats($branchId, $from, $to);

        return response()->json($data);
    }
}
