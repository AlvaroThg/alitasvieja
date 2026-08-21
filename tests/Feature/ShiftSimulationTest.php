<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Branch;
use App\Models\Table;
use App\Modules\Menu\Models\Category;
use App\Modules\Menu\Models\Product;
use App\Modules\Menu\Models\ProductVariant;
use App\Modules\Orders\Services\CheckoutService;
use App\Modules\Orders\Services\OrderService;
use App\Modules\Cash\Services\CashService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShiftSimulationTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_shift_simulation()
    {
        // 1. Configuración de datos básicos
        $branch = Branch::create([
            'name' => 'Sucursal Principal',
            'slug' => 'principal',
            'address' => 'Av. Central 123',
            'city' => 'Ciudad',
            'phone' => '1234567',
            'is_active' => true
        ]);
        
        $user = User::factory()->create([
            'role' => 'cashier',
            'branch_id' => $branch->id,
            'is_active' => true
        ]);

        $this->actingAs($user);

        $table = Table::create([
            'branch_id' => $branch->id,
            'name' => 'Mesa 1',
            'status' => 'available'
        ]);

        $category = Category::create([
            'name' => 'Alitas',
            'slug' => 'alitas'
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Alitas Clásicas',
            'slug' => 'alitas-clasicas',
            'is_wings' => true,
            'has_sauces' => true,
            'is_active' => true
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => '6 Piezas',
            'sku' => 'AL-CL-6',
            'price' => 30.00,
            'wings_count' => 6,
            'max_sauces' => 1,
            'is_active' => true
        ]);

        // Instanciar servicios
        $cashService = app(CashService::class);
        $orderService = app(OrderService::class);
        $checkoutService = app(CheckoutService::class);

        // ==========================================
        // PASO 1: Apertura de Caja
        // ==========================================
        $cashSession = $cashService->openSession($branch->id, $user->id, 500.00);
        $this->assertNotNull($cashSession);
        $this->assertEquals(500.00, $cashSession->opening_amount);

        // ==========================================
        // PASO 2: Toma de pedido para MESA (Dine-in)
        // ==========================================
        $order1 = $orderService->createOrder(
            $branch->id,
            $table->id,
            $user->id,
            'Cliente en mesa',
            'dine_in'
        );
        $this->assertEquals('open', $order1->status);
        $this->assertEquals('dine_in', $order1->order_type);

        // Simular que agregaron el producto al carrito (qty = 1)
        $orderService->addItem($order1, [
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'notes' => ''
        ]);
        // El total de la orden 1 debe ser 30.00
        $order1->refresh();
        $this->assertEquals(30.00, $order1->total);

        // Cobrar pedido de la mesa con EFECTIVO
        $checkoutService->processPayment($order1, [
            ['method' => 'cash', 'amount' => 30.00]
        ]);
        $order1->refresh();
        $this->assertEquals('paid', $order1->status);

        // ==========================================
        // PASO 3: Pedido PARA RECOGER (Takeaway)
        // ==========================================
        $order2 = $orderService->createOrder(
            $branch->id,
            null,
            $user->id,
            'Juan Perez (Recoger)',
            'takeaway'
        );
        $this->assertEquals('takeaway', $order2->order_type);
        
        $orderService->addItem($order2, [
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'notes' => ''
        ]); // qty = 2 -> 60.00
        $order2->refresh();
        $this->assertEquals(60.00, $order2->total);

        // Cobrar pedido para recoger con QR
        $checkoutService->processPayment($order2, [
            ['method' => 'qr', 'amount' => 60.00]
        ]);
        $order2->refresh();
        $this->assertEquals('paid', $order2->status);

        // ==========================================
        // PASO 4: Pedido DELIVERY
        // ==========================================
        $order3 = $orderService->createOrder(
            $branch->id,
            null,
            $user->id,
            'Delivery Carlos',
            'delivery'
        );
        $this->assertEquals('delivery', $order3->order_type);
        
        $orderService->addItem($order3, [
            'product_variant_id' => $variant->id,
            'quantity' => 3,
            'notes' => ''
        ]); // qty = 3 -> 90.00
        $order3->refresh();
        $this->assertEquals(90.00, $order3->total);

        // Cobrar pedido delivery PAGO MIXTO (Efectivo y Transferencia)
        $checkoutService->processPayment($order3, [
            ['method' => 'cash', 'amount' => 50.00],
            ['method' => 'transfer', 'amount' => 40.00]
        ]);
        $order3->refresh();
        $this->assertEquals('paid', $order3->status);
        $this->assertEquals('mixed', $order3->payment_method);

        // ==========================================
        // PASO 5: Cierre de Caja
        // ==========================================
        $cashSession->refresh();
        
        // Efectivo esperado:
        // Apertura = 500
        // Order 1 Cash = 30
        // Order 2 QR = 0 (no es efectivo)
        // Order 3 Cash = 50, Transfer = 40
        // Total esperado = 500 + 30 + 50 = 580
        $expectedCash = $cashSession->calculateExpected();
        $this->assertEquals(580.00, $expectedCash);

        // Total en QR debe ser 60 (Order 2)
        $qrTotal = $cashSession->getTotalByPaymentMethod('qr');
        $this->assertEquals(60.00, $qrTotal);

        // Cierre con un pequeño faltante de 10 Bs (Cajera contó 570)
        $closedSession = $cashService->closeSession($cashSession, $user->id, 570.00, 'Faltó 10 bs');
        
        $this->assertEquals(570.00, $closedSession->closing_amount);
        $this->assertEquals(-10.00, $closedSession->difference);
        $this->assertNotNull($closedSession->closed_at);
        $this->assertEquals('Faltó 10 bs', $closedSession->notes);
    }
}
