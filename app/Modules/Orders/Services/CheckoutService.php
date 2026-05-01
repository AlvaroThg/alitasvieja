<?php

namespace App\Modules\Orders\Services;

use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Models\OrderPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
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

        // ─── Proceso de pago ──────────────────────────────────────

        DB::transaction(function () use ($order, $payments) {
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

            // MODIFICADO: Descontar inventario
            try {
                $inventoryService = app(\App\Modules\Inventory\Services\InventoryService::class);
                $inventoryService->decrementOnSale($order);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Error descontando inventario para el pedido {$order->order_number}: " . $e->getMessage());
            }
            // FIN MODIFICADO
        });
    }
}
