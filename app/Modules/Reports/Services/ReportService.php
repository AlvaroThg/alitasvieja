<?php

namespace App\Modules\Reports\Services;

use App\Modules\Orders\Models\Order;
use Carbon\Carbon;
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
}
