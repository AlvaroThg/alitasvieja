<?php

namespace App\Modules\Orders\Services;

use App\Modules\Cash\Models\CashSession;
use App\Modules\Cash\Services\CashService;
use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Models\OrderPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(private CashService $cashService)
    {
    }

    /**
     * Devuelve la caja abierta de la sucursal o falla.
     *
     * No se puede cobrar con la caja cerrada: el dinero entraría al negocio sin
     * quedar reflejado en ningún arqueo. Se expone público para que el POS pueda
     * avisar al cajero ANTES de armar el cobro.
     *
     * @throws ValidationException
     */
    public function requireOpenSession(int $branchId): CashSession
    {
        $session = $this->cashService->getActiveSession($branchId);

        if (!$session) {
            throw ValidationException::withMessages([
                'cash_session' => 'No hay una caja abierta en esta sucursal. Abre la caja antes de cobrar.',
            ]);
        }

        return $session;
    }

    /**
     * Procesa el pago de un pedido.
     *
     * @param  Order  $order
     * @param  array  $payments  [['method'=>'cash'|'card'|'qr'|'transfer', 'amount'=>float, 'reference'=>?string], ...]
     *
     * @throws ValidationException
     */
    public function processPayment(Order $order, array $payments): void
    {
        // ─── Validaciones ─────────────────────────────────────────

        if ($order->status !== 'open') {
            throw ValidationException::withMessages([
                'order' => 'Solo se pueden cobrar pedidos en estado abierto.',
            ]);
        }

        if (empty($payments)) {
            throw ValidationException::withMessages([
                'payments' => 'Debe especificar al menos un método de pago.',
            ]);
        }

        $totalPaid = 0.0;
        $validMethods = ['cash', 'card', 'qr', 'transfer'];

        foreach ($payments as $index => $payment) {
            $position = $index + 1;

            if (!isset($payment['method']) || !in_array($payment['method'], $validMethods)) {
                throw ValidationException::withMessages([
                    "payments.{$index}.method" => "El método de pago #{$position} no es válido. Use: cash, card, qr o transfer.",
                ]);
            }

            if (!isset($payment['amount']) || $payment['amount'] <= 0) {
                throw ValidationException::withMessages([
                    "payments.{$index}.amount" => "El monto del pago #{$position} debe ser mayor a 0.",
                ]);
            }

            $totalPaid += (float) $payment['amount'];
        }

        // Validar que la suma de pagos cubra el total (margen de flotante ±0.01)
        if (abs($totalPaid - (float) $order->total) > 0.01) {
            throw ValidationException::withMessages([
                'payments' => 'El monto no cubre el total del pedido.',
            ]);
        }

        // Regla de negocio: solo se cobra con la caja abierta.
        $session = $this->requireOpenSession($order->branch_id);

        // ─── Proceso de pago ──────────────────────────────────────

        DB::transaction(function () use ($order, $payments, $session) {
            // Determinar payment_method del pedido
            if (count($payments) > 1) {
                $paymentMethod = 'mixed';
            } else {
                $paymentMethod = $payments[0]['method'];
            }

            // Crear cada registro de pago
            foreach ($payments as $payment) {
                OrderPayment::create([
                    'order_id'  => $order->id,
                    'method'    => $payment['method'],
                    'amount'    => $payment['amount'],
                    'reference' => $payment['reference'] ?? null,
                ]);
            }

            // Actualizar el pedido
            $order->update([
                'payment_method' => $paymentMethod,
                'status'         => 'paid',
                'closed_at'      => now(),
            ]);

            // Registrar cada pago en movimientos de caja (con su respectiva etiqueta de caja)
            // de modo que aparezcan en el historial y reportes de movimientos.
            foreach ($payments as $payment) {
                $method = $payment['method'] ?? 'cash';
                $amount = (float) ($payment['amount'] ?? 0);
                if ($amount <= 0) continue;

                $cashBox = match ($method) {
                    'cash'     => 'sales',
                    'qr'       => 'qr',
                    'card'     => 'card',
                    'transfer' => 'transfer_bank',
                    default    => 'sales',
                };

                $label = match ($method) {
                    'cash'     => 'Venta ',
                    'qr'       => 'Venta QR ',
                    'card'     => 'Venta Tarjeta ',
                    'transfer' => 'Venta Transf. ',
                    default    => 'Venta ',
                };

                $this->cashService->registerSaleIncome(
                    $session,
                    $amount,
                    $label . $order->order_number,
                    $order->order_number,
                    auth()->id() ?? $order->user_id,
                    $cashBox
                );
            }

            // NOTA: el inventario se descuenta al ENVIAR A COCINA (submitOrder),
            // no aquí, para que el stock refleje el consumo apenas se hace el pedido.
        });
    }
}
