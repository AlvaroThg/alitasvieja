<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Branch;
use App\Modules\Orders\Services\CheckoutService;
use App\Modules\Orders\Services\OrderService;
use App\Modules\Orders\Services\WingSauceValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SalesFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_sales_flow_with_sauces()
    {
        // 1. Setup inicial
        $branch = Branch::create([
            'name' => 'Sucursal Central',
            'slug' => 'central',
            'address' => '...',
            'city' => '...',
            'phone' => '...',
            'is_active' => true
        ]);
        
        $user = User::factory()->create([
            'role' => 'cashier',
            'branch_id' => $branch->id,
            'is_active' => true
        ]);

        $this->actingAs($user);

        // Abrir caja para la sucursal
        $cashService = app(\App\Modules\Cash\Services\CashService::class);
        $cashSession = $cashService->openSession($branch->id, $user->id, 1000);

        // 2. Simular un producto (Alitas) y validación de salsas
        $validator = app(WingSauceValidator::class);
        
        $variant = new \stdClass();
        $variant->wings_count = 6;
        $variant->max_sauces = 2;

        $saucesData = [
            [
                'sauce_id' => 1,
                'quantity' => 4,
                'is_coated' => true,
            ],
            [
                'sauce_id' => 2,
                'quantity' => 2,
                'is_coated' => false,
            ]
        ];

        // Regla: la suma total es 6 (4 + 2). Esto no debe arrojar excepción de validación.
        $extraCharge = $validator->validate($variant, $branch->id, $saucesData);
        
        $this->assertEquals(0, $extraCharge, 'No debería cobrar extra por salsas en la sucursal por defecto');

        // 3. Crear orden
        $orderService = app(OrderService::class);
        $order = $orderService->createOrder(
            $branch->id,
            null, // tableId = null (para llevar)
            $user->id,
            'Orden de prueba',
            'takeaway'
        );

        // La orden debe estar creada con estado open
        $this->assertEquals('open', $order->status);

        // 4. Cobrar orden
        $checkoutService = app(CheckoutService::class);
        
        // Simular que la orden tiene total 50 en la BD
        $order->update(['total' => 50.00]);

        $payments = [
            ['method' => 'cash', 'amount' => 50.00]
        ];

        $checkoutService->processPayment($order, $payments);

        // La orden ahora debe estar pagada
        $order->refresh();
        $this->assertEquals('paid', $order->status);

        // Verificar que el movimiento de caja se registró
        $movements = $cashSession->movements()->get();
        $this->assertGreaterThanOrEqual(1, $movements->count());
        
        $paymentMovement = $movements->last();
        $this->assertEquals(50.00, $paymentMovement->amount);
    }
}
