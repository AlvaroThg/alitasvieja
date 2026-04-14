<?php

namespace App\Modules\Orders\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    protected CheckoutService $checkoutService;

    public function __construct(CheckoutService $checkoutService)
    {
        $this->checkoutService = $checkoutService;
    }

    /**
     * GET /pos/checkout/{order}
     * Muestra la vista de checkout (el frontend es Livewire).
     */
    public function show(Order $order): View
    {
        $order->load(['items.productVariant.product', 'branch']);

        return view('pos.checkout', compact('order'));
    }

    /**
     * POST /pos/checkout/{order}/pay
     * Procesa el pago del pedido.
     */
    public function process(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'payments'              => 'required|array|min:1',
            'payments.*.method'     => 'required|in:cash,card,qr,transfer',
            'payments.*.amount'     => 'required|numeric|min:0.01',
            'payments.*.reference'  => 'nullable|string|max:100',
        ]);

        try {
            $this->checkoutService->processPayment($order, $validated['payments']);

            return response()->json([
                'success'      => true,
                'order_number' => $order->order_number,
                'redirect'     => '/pos',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }
}
