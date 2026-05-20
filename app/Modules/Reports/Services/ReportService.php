<?php

namespace App\Modules\Reports\Services;

use App\Modules\Orders\Models\Order;
use App\Models\Branch;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class ReportService
{
    /**
     * Resumen de ventas por rango de fechas.
     * Filtra por closed_at (fecha de cierre, no de apertura).
     *
     * @param  int|null  $branchId  NULL = todas las sucursales
     * @return array{total_revenue: float, total_orders: int, average_ticket: float}
     */
    public function getSalesSummary(?int $branchId, Carbon $from, Carbon $to): array
    {
        $query = Order::paid()
            ->whereBetween('closed_at', [$from->startOfDay(), $to->endOfDay()]);

        if ($branchId) {
            $query->byBranch($branchId);
        }

        $totalRevenue = (float) $query->sum('total');
        $totalOrders  = (int) $query->count();
        $averageTicket = $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0.0;

        return [
            'total_revenue'  => $totalRevenue,
            'total_orders'   => $totalOrders,
            'average_ticket' => $averageTicket,
        ];
    }

    /**
     * Productos más vendidos en un rango de fechas.
     *
     * @param  int|null  $branchId  NULL = todas las sucursales
     * @param  int       $limit     Máximo de resultados (default 10)
     * @return Collection
     */
    public function getTopProducts(?int $branchId, Carbon $from, Carbon $to, int $limit = 10): Collection
    {
        $query = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->where('orders.status', 'paid')
            ->whereBetween('orders.closed_at', [$from->startOfDay(), $to->endOfDay()]);

        if ($branchId) {
            $query->where('orders.branch_id', $branchId);
        }

        return $query
            ->groupBy('order_items.product_variant_id', 'product_variants.name')
            ->select([
                'order_items.product_variant_id',
                'product_variants.name',
                DB::raw('SUM(order_items.quantity) as total_vendido'),
                DB::raw('SUM(order_items.subtotal) as revenue'),
            ])
            ->orderByDesc('total_vendido')
            ->limit($limit)
            ->get();
    }

    /**
     * Busca pedidos por order_number o daily_number.
     * Si $query es numérico, también busca por daily_number del día actual.
     *
     * @param  string    $search
     * @param  int|null  $branchId  NULL = todas las sucursales
     * @return Collection
     */
    public function searchByOrderNumber(string $search, ?int $branchId = null): Collection
    {
        $query = Order::with(['branch', 'user:id,name'])
            ->withCount('items');

        if ($branchId) {
            $query->byBranch($branchId);
        }

        $query->where(function ($q) use ($search) {
            // Buscar por order_number (histórico) con LIKE
            $q->where('order_number', 'like', '%' . $search . '%');

            // Si el query es numérico, también buscar por daily_number del día actual
            if (is_numeric($search)) {
                $q->orWhere(function ($sub) use ($search) {
                    $sub->where('daily_number', (int) $search)
                        ->whereDate('opened_at', today());
                });
            }
        });

        return $query->orderByDesc('opened_at')->limit(20)->get();
    }

    /**
     * Pedidos pagados en un rango de fechas, paginados.
     *
     * @param  int|null  $branchId  NULL = todas las sucursales
     * @return LengthAwarePaginator
     */
    public function getOrdersByDateRange(?int $branchId, Carbon $from, Carbon $to): LengthAwarePaginator
    {
        $query = Order::paid()
            ->whereBetween('closed_at', [$from->startOfDay(), $to->endOfDay()])
            ->with(['branch', 'user:id,name'])
            ->withCount('items')
            ->orderByDesc('closed_at');

        if ($branchId) {
            $query->byBranch($branchId);
        }

        return $query->paginate(15);
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // MÉTODOS NUEVOS — Fase 4: Dashboard y Reportes
    // ─────────────────────────────────────────────────────────────────────────────

    // MÉTODO NUEVO — Fase 4
    /**
     * KPIs principales del dashboard con comparación vs período anterior.
     *
     * @param  int|null     $branchId  NULL = todas las sucursales
     * @param  string       $period    'today' | 'week' | 'month' | 'custom'
     * @param  Carbon|null  $from      Requerido si $period = 'custom'
     * @param  Carbon|null  $to        Requerido si $period = 'custom'
     */
    public function getDashboardSummary(
        ?int $branchId,
        string $period = 'today',
        ?Carbon $from = null,
        ?Carbon $to = null
    ): array {
        $tz = 'America/La_Paz';

        // Calcular rango del período actual
        [$currentFrom, $currentTo] = $this->resolvePeriodRange($period, $from, $to, $tz);

        // Calcular rango del período anterior (mismo largo, inmediatamente antes)
        $periodLengthDays = $currentFrom->diffInDays($currentTo) + 1;
        $previousTo   = $currentFrom->copy()->subDay()->endOfDay();
        $previousFrom = $previousTo->copy()->subDays($periodLengthDays - 1)->startOfDay();

        // ── Query del período actual ──
        $currentData = $this->buildDashboardQuery($branchId, $currentFrom, $currentTo);

        // ── Query del período anterior ──
        $previousData = $this->buildDashboardQuery($branchId, $previousFrom, $previousTo);

        // ── Revenue por sucursal ──
        $revenueByBranch = $this->getRevenueByBranch($branchId, $currentFrom, $currentTo);

        // ── Variación porcentual ──
        $previousRevenue = (float) $previousData['total_revenue'];
        $currentRevenue  = (float) $currentData['total_revenue'];

        // Si el período anterior no tiene datos, retornar 0 (no dividir por cero)
        $revenueVsPrevious = $previousRevenue > 0
            ? round((($currentRevenue - $previousRevenue) / $previousRevenue) * 100, 2)
            : 0.0;

        return [
            'total_revenue'       => $currentRevenue,
            'total_orders'        => (int) $currentData['total_orders'],
            'average_ticket'      => (int) $currentData['total_orders'] > 0
                ? round($currentRevenue / (int) $currentData['total_orders'], 2)
                : 0.0,
            'cancelled_orders'    => (int) $currentData['cancelled_orders'],
            'revenue_by_branch'   => $revenueByBranch,
            'revenue_vs_previous' => $revenueVsPrevious,
        ];
    }

    /**
     * Resuelve el rango de fechas según el tipo de período.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolvePeriodRange(string $period, ?Carbon $from, ?Carbon $to, string $tz): array
    {
        return match ($period) {
            'today' => [
                Carbon::now($tz)->startOfDay(),
                Carbon::now($tz)->endOfDay(),
            ],
            'week' => [
                Carbon::now($tz)->startOfWeek(Carbon::MONDAY),
                Carbon::now($tz)->endOfWeek(Carbon::SUNDAY),
            ],
            'month' => [
                Carbon::now($tz)->startOfMonth(),
                Carbon::now($tz)->endOfMonth(),
            ],
            'custom' => [
                $from->copy()->startOfDay(),
                $to->copy()->endOfDay(),
            ],
            // Decisión defensiva: período no reconocido → hoy
            default => [
                Carbon::now($tz)->startOfDay(),
                Carbon::now($tz)->endOfDay(),
            ],
        };
    }

    /**
     * Query base para KPIs del dashboard (pagados + cancelados).
     * Una sola query con agregaciones condicionales para evitar viajes extra a DB.
     */
    private function buildDashboardQuery(?int $branchId, Carbon $from, Carbon $to): array
    {
        $query = DB::table('orders')
            ->whereBetween('closed_at', [$from, $to])
            ->whereIn('status', ['paid', 'cancelled']);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $result = $query->select([
            DB::raw("COALESCE(SUM(CASE WHEN status = 'paid' THEN total ELSE 0 END), 0) as total_revenue"),
            DB::raw("COUNT(CASE WHEN status = 'paid' THEN 1 END) as total_orders"),
            DB::raw("COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_orders"),
        ])->first();

        return [
            'total_revenue'    => (float) $result->total_revenue,
            'total_orders'     => (int) $result->total_orders,
            'cancelled_orders' => (int) $result->cancelled_orders,
        ];
    }

    /**
     * Revenue desglosado por sucursal activa.
     * Una sola query agrupada por branch_id, evita N+1.
     */
    private function getRevenueByBranch(?int $branchId, Carbon $from, Carbon $to): array
    {
        $query = DB::table('orders')
            ->join('branches', 'orders.branch_id', '=', 'branches.id')
            ->where('orders.status', 'paid')
            ->where('branches.is_active', true)
            ->whereBetween('orders.closed_at', [$from, $to]);

        if ($branchId) {
            $query->where('orders.branch_id', $branchId);
        }

        return $query
            ->groupBy('orders.branch_id', 'branches.name')
            ->select([
                'orders.branch_id',
                'branches.name as branch_name',
                DB::raw('COALESCE(SUM(orders.total), 0) as revenue'),
                DB::raw('COUNT(orders.id) as orders'),
            ])
            ->orderByDesc('revenue')
            ->get()
            ->map(fn ($row) => [
                'branch_id'   => (int) $row->branch_id,
                'branch_name' => $row->branch_name,
                'revenue'     => (float) $row->revenue,
                'orders'      => (int) $row->orders,
            ])
            ->toArray();
    }

    // MÉTODO NUEVO — Fase 4
    /**
     * Desglose de pagos por método de pago.
     *
     * IMPORTANTE: usa la tabla order_payments (no orders.payment_method)
     * porque un pedido 'mixed' genera múltiples registros en order_payments.
     * Se joinea con orders para filtrar por branch, status y rango de fecha.
     */
    public function getPaymentMethodBreakdown(?int $branchId, Carbon $from, Carbon $to): array
    {
        $query = DB::table('order_payments')
            ->join('orders', 'orders.id', '=', 'order_payments.order_id')
            ->where('orders.status', 'paid')
            ->whereBetween('orders.closed_at', [$from->startOfDay(), $to->endOfDay()]);

        if ($branchId) {
            $query->where('orders.branch_id', $branchId);
        }

        $results = $query
            ->groupBy('order_payments.method')
            ->select([
                'order_payments.method',
                DB::raw('COALESCE(SUM(order_payments.amount), 0) as total_amount'),
                DB::raw('COUNT(order_payments.id) as transaction_count'),
            ])
            ->orderByDesc('total_amount')
            ->get();

        // Calcular porcentaje respecto al total general
        $grandTotal = $results->sum('total_amount');

        return $results->map(fn ($row) => [
            'method'            => $row->method,
            'total_amount'      => (float) $row->total_amount,
            'transaction_count' => (int) $row->transaction_count,
            'percentage'        => $grandTotal > 0
                ? round(((float) $row->total_amount / $grandTotal) * 100, 2)
                : 0.0,
        ])->toArray();
    }

    // MÉTODO NUEVO — Fase 4
    /**
     * Estadísticas específicas de alitas.
     *
     * Joins: order_items → orders (status='paid', rango, branch)
     *        order_items → product_variants → products (is_wings=TRUE)
     *
     * Para sauce_usage: order_item_sauces → order_items → orders
     */
    public function getWingStatistics(?int $branchId, Carbon $from, Carbon $to): array
    {
        $dateRange = [$from->startOfDay(), $to->endOfDay()];

        // ── Base query: ítems de alitas en pedidos pagados del rango ──
        $baseWingQuery = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('product_variants', 'product_variants.id', '=', 'order_items.product_variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->where('orders.status', 'paid')
            ->where('products.is_wings', true)
            ->whereBetween('orders.closed_at', $dateRange);

        if ($branchId) {
            $baseWingQuery->where('orders.branch_id', $branchId);
        }

        // ── Total alitas vendidas y pedidos con alitas ──
        $summary = (clone $baseWingQuery)->select([
            DB::raw('COALESCE(SUM(order_items.quantity * product_variants.wings_count), 0) as total_wings_sold'),
            DB::raw('COUNT(DISTINCT orders.id) as total_wing_orders'),
        ])->first();

        // ── Top 5 combos más pedidos ──
        $topCombos = (clone $baseWingQuery)
            ->groupBy('product_variants.id', 'product_variants.name')
            ->select([
                'product_variants.id as variant_id',
                'product_variants.name as variant_name',
                DB::raw('SUM(order_items.quantity) as qty_sold'),
                DB::raw('SUM(order_items.subtotal) as revenue'),
            ])
            ->orderByDesc('qty_sold')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'variant_id'   => (int) $row->variant_id,
                'variant_name' => $row->variant_name,
                'qty_sold'     => (int) $row->qty_sold,
                'revenue'      => (float) $row->revenue,
            ])
            ->toArray();

        // ── Uso de salsas (bañadas + aparte) ──
        // Join: order_item_sauces → order_items → orders
        // Traer TODAS las salsas usadas en pedidos pagados del rango
        $sauceQuery = DB::table('order_item_sauces')
            ->join('order_items', 'order_items.id', '=', 'order_item_sauces.order_item_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('sauces', 'sauces.id', '=', 'order_item_sauces.sauce_id')
            ->where('orders.status', 'paid')
            ->whereBetween('orders.closed_at', $dateRange);

        if ($branchId) {
            $sauceQuery->where('orders.branch_id', $branchId);
        }

        $sauceUsage = $sauceQuery
            ->groupBy('order_item_sauces.sauce_id', 'sauces.name')
            ->select([
                'order_item_sauces.sauce_id',
                'sauces.name as sauce_name',
                DB::raw("COALESCE(SUM(CASE WHEN order_item_sauces.is_coated = 1 THEN order_item_sauces.quantity ELSE 0 END), 0) as coated_count"),
                DB::raw("COALESCE(SUM(CASE WHEN order_item_sauces.is_coated = 0 THEN order_item_sauces.quantity ELSE 0 END), 0) as side_count"),
                DB::raw("COALESCE(SUM(order_item_sauces.quantity), 0) as total_uses"),
            ])
            ->orderByDesc('total_uses')
            ->get()
            ->map(fn ($row) => [
                'sauce_id'     => (int) $row->sauce_id,
                'sauce_name'   => $row->sauce_name,
                'coated_count' => (int) $row->coated_count,
                'side_count'   => (int) $row->side_count,
                'total_uses'   => (int) $row->total_uses,
            ])
            ->toArray();

        // ── Revenue por cargo extra de salsas ──
        $extraSauceQuery = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'paid')
            ->whereBetween('orders.closed_at', $dateRange);

        if ($branchId) {
            $extraSauceQuery->where('orders.branch_id', $branchId);
        }

        $extraSauceRevenue = (float) $extraSauceQuery
            ->sum('order_items.extra_sauce_charge');

        return [
            'total_wings_sold'    => (int) $summary->total_wings_sold,
            'total_wing_orders'   => (int) $summary->total_wing_orders,
            'top_combos'          => $topCombos,
            'sauce_usage'         => $sauceUsage,
            'extra_sauce_revenue' => $extraSauceRevenue,
        ];
    }

    // MÉTODO NUEVO — Fase 4
    /**
     * Serie temporal de ventas para graficar.
     *
     * @param  string  $groupBy  'day' | 'week' | 'month'
     * @return Collection  [['period' => string, 'revenue' => float, 'orders' => int], ...]
     *
     * INCLUYE períodos sin ventas (revenue=0, orders=0) para que el frontend
     * grafique la serie completa sin huecos. Se genera el rango en PHP con
     * CarbonPeriod y se hace merge con los resultados SQL.
     */
    public function getSalesByPeriod(
        ?int $branchId,
        Carbon $from,
        Carbon $to,
        string $groupBy = 'day'
    ): Collection {
        $startOfDay = $from->copy()->startOfDay();
        $endOfDay   = $to->copy()->endOfDay();

        // ── Query SQL con agregación directa usando selectRaw ──
        // Se usan funciones MySQL (DATE, WEEK, DATE_FORMAT).
        // WEEK(..., 1) usa modo ISO (lunes = primer día de semana).
        $groupExpr = $this->getGroupByExpression($groupBy);

        $sqlData = DB::table('orders')
            ->where('status', 'paid')
            ->whereBetween('closed_at', [$startOfDay, $endOfDay])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->groupByRaw($groupExpr)
            ->selectRaw("{$groupExpr} as period, COALESCE(SUM(total), 0) as revenue, COUNT(id) as orders")
            ->get()
            ->keyBy('period');

        // ── Generar rango completo con CarbonPeriod y rellenar huecos ──
        $allPeriods = $this->generatePeriodLabels($from, $to, $groupBy);

        return collect($allPeriods)->map(fn (string $label) => [
            'period'  => $label,
            'revenue' => isset($sqlData[$label]) ? (float) $sqlData[$label]->revenue : 0.0,
            'orders'  => isset($sqlData[$label]) ? (int) $sqlData[$label]->orders : 0,
        ]);
    }

    /**
     * Retorna la expresión SQL de agrupación como string crudo.
     */
    private function getGroupByExpression(string $groupBy): string
    {
        return match ($groupBy) {
            'week'  => "CONCAT(YEAR(closed_at), '-W', LPAD(WEEK(closed_at, 1), 2, '0'))",
            'month' => "DATE_FORMAT(closed_at, '%Y-%m')",
            default => "DATE(closed_at)",
        };
    }

    /**
     * Genera el array completo de etiquetas de período para rellenar huecos.
     *
     * @return string[]
     */
    private function generatePeriodLabels(Carbon $from, Carbon $to, string $groupBy): array
    {
        $labels = [];

        switch ($groupBy) {
            case 'week':
                $cursor = $from->copy()->startOfWeek(Carbon::MONDAY);
                $end    = $to->copy()->endOfWeek(Carbon::SUNDAY);
                while ($cursor->lte($end)) {
                    $labels[] = $cursor->format('Y') . '-W' . str_pad($cursor->weekOfYear, 2, '0', STR_PAD_LEFT);
                    $cursor->addWeek();
                }
                break;

            case 'month':
                $cursor = $from->copy()->startOfMonth();
                $end    = $to->copy()->endOfMonth();
                while ($cursor->lte($end)) {
                    $labels[] = $cursor->format('Y-m');
                    $cursor->addMonth();
                }
                break;

            default: // 'day'
                $period = CarbonPeriod::create($from->copy()->startOfDay(), $to->copy()->startOfDay());
                foreach ($period as $date) {
                    $labels[] = $date->format('Y-m-d');
                }
                break;
        }

        return $labels;
    }

    // MÉTODO NUEVO — Fase 4
    /**
     * Estadísticas de uso de promociones.
     */
    public function getPromotionStats(?int $branchId, Carbon $from, Carbon $to): array
    {
        $dateRange = [$from->startOfDay(), $to->endOfDay()];

        // ── Totales generales ──
        $totalsQuery = DB::table('order_promotions')
            ->join('orders', 'orders.id', '=', 'order_promotions.order_id')
            ->where('orders.status', 'paid')
            ->whereBetween('orders.closed_at', $dateRange);

        if ($branchId) {
            $totalsQuery->where('orders.branch_id', $branchId);
        }

        $totals = $totalsQuery->select([
            DB::raw('COALESCE(SUM(order_promotions.discount_applied), 0) as total_discount_applied'),
            DB::raw('COUNT(DISTINCT order_promotions.order_id) as promotions_used'),
        ])->first();

        // ── Top 5 promociones más usadas ──
        $topQuery = DB::table('order_promotions')
            ->join('orders', 'orders.id', '=', 'order_promotions.order_id')
            ->join('promotions', 'promotions.id', '=', 'order_promotions.promotion_id')
            ->where('orders.status', 'paid')
            ->whereBetween('orders.closed_at', $dateRange);

        if ($branchId) {
            $topQuery->where('orders.branch_id', $branchId);
        }

        $topPromotions = $topQuery
            ->groupBy('order_promotions.promotion_id', 'promotions.name')
            ->select([
                'order_promotions.promotion_id',
                'promotions.name as promotion_name',
                DB::raw('COUNT(order_promotions.id) as times_used'),
                DB::raw('COALESCE(SUM(order_promotions.discount_applied), 0) as total_discount'),
            ])
            ->orderByDesc('times_used')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'promotion_id'   => (int) $row->promotion_id,
                'promotion_name' => $row->promotion_name,
                'times_used'     => (int) $row->times_used,
                'total_discount' => (float) $row->total_discount,
            ])
            ->toArray();

        return [
            'total_discount_applied' => (float) $totals->total_discount_applied,
            'promotions_used'        => (int) $totals->promotions_used,
            'top_promotions'         => $topPromotions,
        ];
    }
}
