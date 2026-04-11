<?php

namespace App\Modules\Orders\Services;

use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Models\OrderPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    /**
     * Procesa el pago de un pedido, soportando pagos simples y mixtos.
     *
     * @param  Order  $order
     * @param  array  $payments  [['method'=>string, 'amount'=>float, 'reference'=>?string], ...]
     *
     * @throws ValidationException
     */
    public function processPayment(Order $order, array $payments): void
    {
        // ─── Validaciones ─────────────────────────────────────────

        if ($order->status !== 'open') {
            throw ValidationException::withMessages([
                'order' => 'Solo se pueden cobrar pedidos con estado "abierto".',
            ]);
        }

        if (empty($payments)) {
            throw ValidationException::withMessages([
                'payments' => 'Debe proporcionar al menos un pago.',
            ]);
        }

        $totalPaid = 0.0;

        foreach ($payments as $index => $payment) {
            $amount = (float) ($payment['amount'] ?? 0);

            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    "payments.{$index}.amount" => 'El monto de cada pago debe ser mayor a 0.',
                ]);
            }

            $validMethods = ['cash', 'card', 'qr', 'transfer'];
            if (!in_array($payment['method'] ?? '', $validMethods, true)) {
                throw ValidationException::withMessages([
                    "payments.{$index}.method" => 'El método de pago no es válido.',
                ]);
            }

            $totalPaid += $amount;
        }

        // Margen de tolerancia para errores de punto flotante
        if (abs($totalPaid - (float) $order->total) > 0.01) {
            throw ValidationException::withMessages([
                'payments' => 'El monto no cubre el total del pedido.',
            ]);
        }

        // ─── Proceso de pago ──────────────────────────────────────

        DB::transaction(function () use ($order, $payments) {
            // Crear un registro de pago por cada entrada
            foreach ($payments as $payment) {
                OrderPayment::create([
                    'order_id'  => $order->id,
                    'method'    => $payment['method'],
                    'amount'    => $payment['amount'],
                    'reference' => $payment['reference'] ?? null,
                ]);
            }

            // Determinar el método de pago del pedido
            $paymentMethod = count($payments) > 1
                ? 'mixed'
                : $payments[0]['method'];

            // Actualizar el pedido
            $order->update([
                'payment_method' => $paymentMethod,
                'status'         => 'paid',
                'closed_at'      => now(),
            ]);
        });
    }
}
