<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Branch;
use App\Modules\Reports\Services\ReportService;
use Carbon\Carbon;

class AdminDashboard extends Component
{
    // ─── Filtros ──────────────────────────────────────────────
    public string $period = 'today';
    public ?int $branchId = null;

    // ─── Datos del dashboard ─────────────────────────────────
    public array $summary = [];
    public array $revenueByBranch = [];
    public array $topProducts = [];
    public array $paymentBreakdown = [];
    public array $salesSeries = [];
    public array $branches = [];

    protected ReportService $reportService;

    public function boot(ReportService $reportService): void
    {
        $this->reportService = $reportService;
    }

    public function mount(): void
    {
        $this->branches = Branch::active()
            ->select('id', 'name')
            ->get()
            ->toArray();

        $this->loadDashboardData();
    }

    /**
     * Hook: cuando el usuario cambia el período via wire:click.
     */
    public function updatedPeriod(): void
    {
        $this->loadDashboardData();
    }

    /**
     * Hook: cuando el usuario cambia la sucursal via wire:model.live.
     */
    public function updatedBranchId(): void
    {
        // Convertir string vacío a null
        if ($this->branchId === 0 || $this->branchId === '') {
            $this->branchId = null;
        }
        $this->loadDashboardData();
    }

    /**
     * Método público para cambiar período desde wire:click.
     */
    public function setPeriod(string $period): void
    {
        $this->period = $period;
        $this->loadDashboardData();
    }

    /**
     * Carga todos los datos del dashboard usando ReportService.
     */
    public function loadDashboardData(): void
    {
        $tz = 'America/La_Paz';

        // Resolver rango de fechas
        [$from, $to] = $this->resolveDateRange($tz);

        // ── KPIs principales ──
        $this->summary = $this->reportService->getDashboardSummary(
            $this->branchId,
            $this->period
        );

        // ── Revenue por sucursal ──
        $this->revenueByBranch = $this->summary['revenue_by_branch'] ?? [];

        // ── Top productos ──
        $this->topProducts = $this->reportService
            ->getTopProducts($this->branchId, $from, $to, 10)
            ->map(fn ($p) => [
                'name'          => $p->name,
                'total_vendido' => (int) $p->total_vendido,
                'revenue'       => (float) $p->revenue,
            ])
            ->toArray();

        // ── Métodos de pago ──
        $this->paymentBreakdown = $this->reportService
            ->getPaymentMethodBreakdown($this->branchId, $from, $to);

        // ── Serie temporal de ventas ──
        $groupBy = match ($this->period) {
            'month' => 'day',
            'week'  => 'day',
            default => 'day',
        };

        $series = $this->reportService->getSalesByPeriod(
            $this->branchId,
            $from,
            $to,
            $groupBy
        );

        $this->salesSeries = [
            'labels'   => $series->pluck('period')->toArray(),
            'revenue'  => $series->pluck('revenue')->toArray(),
            'orders'   => $series->pluck('orders')->toArray(),
        ];

        // ── Despachar evento para actualizar Chart.js ──
        $this->dispatch('chartsUpdated', [
            'revenueByBranch'  => $this->revenueByBranch,
            'paymentBreakdown' => $this->paymentBreakdown,
            'salesSeries'      => $this->salesSeries,
        ]);
    }

    /**
     * Resuelve el rango de fechas según el período seleccionado.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolveDateRange(string $tz): array
    {
        return match ($this->period) {
            'week' => [
                Carbon::now($tz)->startOfWeek(Carbon::MONDAY),
                Carbon::now($tz)->endOfWeek(Carbon::SUNDAY),
            ],
            'month' => [
                Carbon::now($tz)->startOfMonth(),
                Carbon::now($tz)->endOfMonth(),
            ],
            default => [ // 'today'
                Carbon::now($tz)->startOfDay(),
                Carbon::now($tz)->endOfDay(),
            ],
        };
    }

    public function render()
    {
        return view('livewire.admin.admin-dashboard');
    }
}
