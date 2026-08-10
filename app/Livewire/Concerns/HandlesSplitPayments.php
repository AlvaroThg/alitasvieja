<?php

namespace App\Livewire\Concerns;

/**
 * Cobro repartido en varios métodos de pago (efectivo + QR, etc.).
 *
 * Vive en un trait porque el mismo cobro ocurre en dos pantallas —la mesa y el
 * pedido de cocina— y la lógica no debe duplicarse. La vista compartida está en
 * resources/views/partials/payment-lines.blade.php.
 *
 * El componente que lo use debe indicar cuánto se está cobrando.
 */
trait HandlesSplitPayments
{
    /** @var array<int, array{method: string, amount: mixed}> */
    public array $pagos = [];

    public string $paymentError = '';

    /** Monto total que debe cubrirse con los pagos. */
    abstract protected function montoACobrar(): float;

    /**
     * Deja una sola línea con el total en efectivo: el caso más común se
     * resuelve sin tocar nada.
     */
    protected function iniciarPagos(?float $total = null): void
    {
        $this->paymentError = '';
        $this->pagos = [[
            'method' => 'cash',
            'amount' => number_format($total ?? $this->montoACobrar(), 2, '.', ''),
        ]];
    }

    public function agregarPago(): void
    {
        // La línea nueva viene con lo que falta, que es lo que se va a tipear.
        $this->pagos[] = [
            'method' => 'qr',
            'amount' => number_format(max(0, $this->faltante), 2, '.', ''),
        ];
    }

    public function quitarPago(int $indice): void
    {
        if (count($this->pagos) <= 1) {
            return; // siempre queda al menos una línea
        }

        unset($this->pagos[$indice]);
        $this->pagos = array_values($this->pagos);
    }

    public function getTotalPagadoProperty(): float
    {
        return round(collect($this->pagos)->sum(fn ($p) => (float) ($p['amount'] ?? 0)), 2);
    }

    /** Positivo: falta cobrar. Negativo: se cargó de más. */
    public function getFaltanteProperty(): float
    {
        return round($this->montoACobrar() - $this->totalPagado, 2);
    }

    public function getPagoCubiertoProperty(): bool
    {
        return abs($this->faltante) < 0.01;
    }

    /**
     * Pagos listos para CheckoutService: sin líneas vacías y con monto numérico.
     *
     * @return array<int, array{method: string, amount: float}>
     */
    protected function pagosParaCobro(): array
    {
        return collect($this->pagos)
            ->map(fn ($p) => [
                'method' => $p['method'] ?? 'cash',
                'amount' => round((float) ($p['amount'] ?? 0), 2),
            ])
            ->filter(fn ($p) => $p['amount'] > 0)
            ->values()
            ->all();
    }
}