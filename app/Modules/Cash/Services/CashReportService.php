<?php

namespace App\Modules\Cash\Services;

use App\Modules\Cash\Models\CashSession;
use App\Modules\Orders\Models\Order;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CashReportService
{
    // MÉTODO NUEVO — Fase 4
    /**
     * Historial de sesiones de caja cerradas, paginadas.
     *
     * Usa withSum para cargar total_incomes y total_expenses sin N+1.
     * Solo sesiones con closed_at NOT NULL (cerradas).
     * Filtros: branch_id, rango de closed_at.
     * Ordenado: closed_at DESC.
     */
    public function getSessionHistory(
        ?int $branchId,
        Carbon $from,
        Carbon $to,
        int $perPage = 20
    ): LengthAwarePaginator {
        $query = CashSession::query()
            ->whereNotNull('closed_at')
            ->whereBetween('closed_at', [$from->startOfDay(), $to->endOfDay()])
            ->with([
                'branch:id,name,city',
                'openedBy:id,name',
                'closedBy:id,name',
            ])
            // Cargar totales como atributos calculados con subquery, evita N+1
            ->withSum(['movements as total_incomes' => fn ($q) => $q->where('type', 'income')], 'amount')
            ->withSum(['movements as total_expenses' => fn ($q) => $q->where('type', 'expense')], 'amount')
            ->orderByDesc('closed_at');

        if ($branchId) {
            $query->byBranch($branchId);
        }

        return $query->paginate($perPage);
    }

    // MÉTODO NUEVO — Fase 4
    /**
     * Detalle completo de una sesión de caja para la pantalla de cierre.
     *
     * Retorna: sesión con relaciones, movimientos con usuario,
     * resumen financiero, y pedidos pagados durante la sesión.
     */
    public function getSessionDetail(CashSession $session): array
    {
        // Eager load relaciones necesarias
        $session->load([
            'branch:id,name,city',
            'openedBy:id,name',
            'closedBy:id,name',
        ]);

        // Movimientos con el usuario que los registró
        $movements = $session->movements()
            ->with('user:id,name')
            ->orderBy('created_at')
            ->get();

        // Totales de ingresos y egresos (una query con agregación condicional)
        $totals = DB::table('cash_movements')
            ->where('cash_session_id', $session->id)
            ->select([
                DB::raw("COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as total_incomes"),
                DB::raw("COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as total_expenses"),
            ])
            ->first();

        $totalIncomes  = (float) $totals->total_incomes;
        $totalExpenses = (float) $totals->total_expenses;
        $expectedAmount = (float) $session->opening_amount + $totalIncomes - $totalExpenses;

        // Etiqueta de diferencia
        $differenceLabel = null;
        if ($session->closing_amount !== null && $session->difference !== null) {
            $diff = (float) $session->difference;
            if ($diff > 0) {
                $differenceLabel = 'SOBRANTE';
            } elseif ($diff < 0) {
                $differenceLabel = 'FALTANTE';
            } else {
                $differenceLabel = 'EXACTO';
            }
        }

        // Pedidos pagados DURANTE esta sesión (dentro del rango temporal de la sesión)
        $ordersInSession = DB::table('orders')
            ->where('status', 'paid')
            ->where('branch_id', $session->branch_id)
            ->where('opened_at', '>=', $session->opened_at)
            ->when($session->closed_at, fn ($q) => $q->where('closed_at', '<=', $session->closed_at))
            ->select([
                DB::raw('COUNT(id) as count'),
                DB::raw('COALESCE(SUM(total), 0) as revenue'),
            ])
            ->first();

        return [
            'session'   => $session,
            'movements' => $movements,
            'summary'   => [
                'opening_amount'  => (float) $session->opening_amount,
                'total_incomes'   => $totalIncomes,
                'total_expenses'  => $totalExpenses,
                'expected_amount' => $expectedAmount,
                'closing_amount'  => $session->closing_amount !== null ? (float) $session->closing_amount : null,
                'difference'      => $session->difference !== null ? (float) $session->difference : null,
                'difference_label' => $differenceLabel,
            ],
            'orders_in_session' => [
                'count'   => (int) $ordersInSession->count,
                'revenue' => (float) $ordersInSession->revenue,
            ],
        ];
    }

    // MÉTODO NUEVO — Fase 4
    /**
     * Exporta el historial de sesiones de caja a CSV.
     *
     * Retorna contenido CSV como string (el controller se encarga del Response).
     * Columnas: Sucursal, Fecha apertura, Fecha cierre, Abierto por, Cerrado por,
     *           Monto inicial, Ingresos, Egresos, Monto esperado, Monto real,
     *           Diferencia, Estado diferencia, Notas
     *
     * Fechas en formato d/m/Y H:i timezone America/La_Paz.
     * Sin librerías externas: usa fputcsv nativo de PHP.
     */
    public function exportSessionsCsv(?int $branchId, Carbon $from, Carbon $to): string
    {
        $tz = 'America/La_Paz';

        // Traer todas las sesiones cerradas con totales (sin paginación)
        $sessions = CashSession::query()
            ->whereNotNull('closed_at')
            ->whereBetween('closed_at', [$from->startOfDay(), $to->endOfDay()])
            ->with([
                'branch:id,name',
                'openedBy:id,name',
                'closedBy:id,name',
            ])
            ->withSum(['movements as total_incomes' => fn ($q) => $q->where('type', 'income')], 'amount')
            ->withSum(['movements as total_expenses' => fn ($q) => $q->where('type', 'expense')], 'amount')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderByDesc('closed_at')
            ->get();

        // Construir CSV con fputcsv
        $output = fopen('php://temp', 'r+');

        // BOM UTF-8 para Excel
        fwrite($output, "\xEF\xBB\xBF");

        // Encabezados
        fputcsv($output, [
            'Sucursal',
            'Fecha apertura',
            'Fecha cierre',
            'Abierto por',
            'Cerrado por',
            'Monto inicial',
            'Ingresos',
            'Egresos',
            'Monto esperado',
            'Monto real',
            'Diferencia',
            'Estado diferencia',
            'Notas',
        ]);

        foreach ($sessions as $session) {
            $totalIncomes  = (float) ($session->total_incomes ?? 0);
            $totalExpenses = (float) ($session->total_expenses ?? 0);
            $expectedAmount = (float) $session->opening_amount + $totalIncomes - $totalExpenses;

            // Estado de diferencia
            $differenceLabel = '';
            if ($session->difference !== null) {
                $diff = (float) $session->difference;
                if ($diff > 0) {
                    $differenceLabel = 'SOBRANTE';
                } elseif ($diff < 0) {
                    $differenceLabel = 'FALTANTE';
                } else {
                    $differenceLabel = 'EXACTO';
                }
            }

            fputcsv($output, [
                $session->branch?->name ?? 'N/A',
                $session->opened_at?->timezone($tz)->format('d/m/Y H:i') ?? '',
                $session->closed_at?->timezone($tz)->format('d/m/Y H:i') ?? '',
                $session->openedBy?->name ?? 'N/A',
                $session->closedBy?->name ?? 'N/A',
                number_format((float) $session->opening_amount, 2, '.', ''),
                number_format($totalIncomes, 2, '.', ''),
                number_format($totalExpenses, 2, '.', ''),
                number_format($expectedAmount, 2, '.', ''),
                $session->closing_amount !== null ? number_format((float) $session->closing_amount, 2, '.', '') : '',
                $session->difference !== null ? number_format((float) $session->difference, 2, '.', '') : '',
                $differenceLabel,
                $session->notes ?? '',
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
