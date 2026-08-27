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
use App\Modules\Cash\Models\CashSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CashServiceTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;
    private User $user;
    private CashService $cashService;
    private OrderService $orderService;
    private CheckoutService $checkoutService;
    private ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'Sucursal Test',
            'slug' => 'test',
            'address' => 'Calle Test 123',
            'city' => 'Ciudad Test',
            'phone' => '1234567',
            'is_active' => true,
        ]);

        $this->user = User::factory()->create([
            'role' => 'cashier',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);

        $this->actingAs($this->user);

        $category = Category::create([
            'name' => 'Alitas',
            'slug' => 'alitas',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Alitas Clásicas',
            'slug' => 'alitas-clasicas',
            'is_wings' => true,
            'has_sauces' => true,
            'is_active' => true,
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => '6 Piezas',
            'sku' => 'AL-CL-6',
            'price' => 30.00,
            'wings_count' => 6,
            'max_sauces' => 1,
            'is_active' => true,
        ]);

        $this->cashService = app(CashService::class);
        $this->orderService = app(OrderService::class);
        $this->checkoutService = app(CheckoutService::class);
    }

    // ─── APERTURA DE CAJA ─────────────────────────────────────

    public function test_open_session_successfully()
    {
        $session = $this->cashService->openSession($this->branch->id, $this->user->id, 500.00);

        $this->assertNotNull($session);
        $this->assertEquals(500.00, $session->opening_amount);
        $this->assertTrue($session->is_open);
        $this->assertNotNull($session->opened_at);
        $this->assertEquals($this->user->id, $session->opened_by);
    }

    public function test_cannot_open_two_sessions()
    {
        $this->cashService->openSession($this->branch->id, $this->user->id, 500.00);

        $this->expectException(ValidationException::class);
        $this->cashService->openSession($this->branch->id, $this->user->id, 200.00);
    }

    public function test_cannot_open_with_negative_amount()
    {
        $this->expectException(ValidationException::class);
        $this->cashService->openSession($this->branch->id, $this->user->id, -100.00);
    }

    // ─── MOVIMIENTOS DE CAJA ──────────────────────────────────

    public function test_add_income_movement()
    {
        $session = $this->cashService->openSession($this->branch->id, $this->user->id, 500.00);

        $movement = $this->cashService->addMovement($session, $this->user->id, [
            'type' => 'income',
            'amount' => 100.00,
            'concept' => 'Ingreso extra',
        ]);

        $this->assertEquals('income', $movement->type);
        $this->assertEquals(100.00, $movement->amount);
    }

    public function test_add_expense_movement()
    {
        $session = $this->cashService->openSession($this->branch->id, $this->user->id, 500.00);

        $movement = $this->cashService->addMovement($session, $this->user->id, [
            'type' => 'expense',
            'amount' => 50.00,
            'concept' => 'Compra de servilletas',
        ]);

        $this->assertEquals('expense', $movement->type);
        $this->assertEquals(50.00, $movement->amount);
    }

    public function test_cannot_add_movement_to_closed_session()
    {
        $session = $this->cashService->openSession($this->branch->id, $this->user->id, 500.00);
        $this->cashService->closeSession($session, $this->user->id, 500.00);

        $this->expectException(ValidationException::class);
        $this->cashService->addMovement($session->fresh(), $this->user->id, [
            'type' => 'income',
            'amount' => 100.00,
            'concept' => 'No debería entrar',
        ]);
    }

    public function test_cannot_add_zero_amount_movement()
    {
        $session = $this->cashService->openSession($this->branch->id, $this->user->id, 500.00);

        $this->expectException(ValidationException::class);
        $this->cashService->addMovement($session, $this->user->id, [
            'type' => 'income',
            'amount' => 0,
            'concept' => 'Monto cero',
        ]);
    }

    public function test_cannot_add_movement_without_concept()
    {
        $session = $this->cashService->openSession($this->branch->id, $this->user->id, 500.00);

        $this->expectException(ValidationException::class);
        $this->cashService->addMovement($session, $this->user->id, [
            'type' => 'income',
            'amount' => 50.00,
            'concept' => '',
        ]);
    }

    // ─── VENTAS Y EFECTIVO ESPERADO ──────────────────────────

    public function test_expected_cash_after_sales()
    {
        $session = $this->cashService->openSession($this->branch->id, $this->user->id, 500.00);

        // Venta 1: efectivo $30
        $order1 = $this->orderService->createOrder($this->branch->id, null, $this->user->id, null, 'takeaway');
        $this->orderService->addItem($order1, ['product_variant_id' => $this->variant->id, 'quantity' => 1]);
        $order1->refresh();
        $this->checkoutService->processPayment($order1, [['method' => 'cash', 'amount' => 30.00]]);

        // Venta 2: QR $60 (no es efectivo)
        $order2 = $this->orderService->createOrder($this->branch->id, null, $this->user->id, null, 'takeaway');
        $this->orderService->addItem($order2, ['product_variant_id' => $this->variant->id, 'quantity' => 2]);
        $order2->refresh();
        $this->checkoutService->processPayment($order2, [['method' => 'qr', 'amount' => 60.00]]);

        // Venta 3: mixto, $50 cash + $40 transfer
        $order3 = $this->orderService->createOrder($this->branch->id, null, $this->user->id, null, 'delivery');
        $this->orderService->addItem($order3, ['product_variant_id' => $this->variant->id, 'quantity' => 3]);
        $order3->refresh();
        $this->checkoutService->processPayment($order3, [
            ['method' => 'cash', 'amount' => 50.00],
            ['method' => 'transfer', 'amount' => 40.00],
        ]);

        $session->refresh();
        // Efectivo esperado: 500 (apertura) + 30 (venta1) + 50 (venta3) = 580
        $this->assertEquals(580.00, $session->calculateExpected());

        // QR total = 60
        $this->assertEquals(60.00, $session->getTotalByPaymentMethod('qr'));

        // Transfer total = 40
        $this->assertEquals(40.00, $session->getTotalByPaymentMethod('transfer'));
    }

    // ─── CIERRE DE CAJA ──────────────────────────────────────

    public function test_close_session_exact()
    {
        $session = $this->cashService->openSession($this->branch->id, $this->user->id, 500.00);

        $closed = $this->cashService->closeSession($session, $this->user->id, 500.00);

        $this->assertEquals(500.00, $closed->closing_amount);
        $this->assertEquals(0.00, $closed->difference);
        $this->assertNotNull($closed->closed_at);
        $this->assertFalse($closed->is_open);
    }

    public function test_close_session_with_shortage()
    {
        $session = $this->cashService->openSession($this->branch->id, $this->user->id, 500.00);

        $closed = $this->cashService->closeSession($session, $this->user->id, 490.00, 'Faltaron 10 Bs');

        $this->assertEquals(490.00, $closed->closing_amount);
        $this->assertEquals(-10.00, $closed->difference);
        $this->assertEquals('Faltaron 10 Bs', $closed->notes);
    }

    public function test_close_session_with_surplus()
    {
        $session = $this->cashService->openSession($this->branch->id, $this->user->id, 500.00);

        $closed = $this->cashService->closeSession($session, $this->user->id, 510.00, 'Sobraron 10 Bs');

        $this->assertEquals(510.00, $closed->closing_amount);
        $this->assertEquals(10.00, $closed->difference);
    }

    public function test_cannot_close_already_closed_session()
    {
        $session = $this->cashService->openSession($this->branch->id, $this->user->id, 500.00);
        $this->cashService->closeSession($session, $this->user->id, 500.00);

        $this->expectException(ValidationException::class);
        $this->cashService->closeSession($session->fresh(), $this->user->id, 500.00);
    }

    public function test_cannot_close_with_negative_amount()
    {
        $session = $this->cashService->openSession($this->branch->id, $this->user->id, 500.00);

        $this->expectException(ValidationException::class);
        $this->cashService->closeSession($session, $this->user->id, -50.00);
    }

    public function test_cannot_close_with_open_orders()
    {
        $session = $this->cashService->openSession($this->branch->id, $this->user->id, 500.00);

        // Crear pedido abierto (sin cobrar)
        $order = $this->orderService->createOrder($this->branch->id, null, $this->user->id, null, 'takeaway');
        $this->orderService->addItem($order, ['product_variant_id' => $this->variant->id, 'quantity' => 1]);

        $this->expectException(ValidationException::class);
        $this->cashService->closeSession($session, $this->user->id, 500.00);
    }

    // ─── RESUMEN DE CAJA ─────────────────────────────────────

    public function test_get_summary()
    {
        $session = $this->cashService->openSession($this->branch->id, $this->user->id, 300.00);

        $summary = $this->cashService->getSummary($session);

        $this->assertEquals(300.00, $summary['opening_amount']);
        $this->assertArrayHasKey('total_incomes', $summary);
        $this->assertArrayHasKey('total_expenses', $summary);
        $this->assertArrayHasKey('expected_amount', $summary);
        $this->assertArrayHasKey('movements', $summary);
    }

    // ─── SESIÓN ACTIVA ───────────────────────────────────────

    public function test_get_active_session()
    {
        $this->assertNull($this->cashService->getActiveSession($this->branch->id));

        $session = $this->cashService->openSession($this->branch->id, $this->user->id, 500.00);

        $active = $this->cashService->getActiveSession($this->branch->id);
        $this->assertNotNull($active);
        $this->assertEquals($session->id, $active->id);
    }

    public function test_no_active_session_after_close()
    {
        $session = $this->cashService->openSession($this->branch->id, $this->user->id, 500.00);
        $this->cashService->closeSession($session, $this->user->id, 500.00);

        $this->assertNull($this->cashService->getActiveSession($this->branch->id));
    }

    // ─── FLUJO COMPLETO DE CAJA ──────────────────────────────

    public function test_full_cash_flow()
    {
        // 1. Abrir caja con Bs. 200
        $session = $this->cashService->openSession($this->branch->id, $this->user->id, 200.00);
        $this->assertTrue($session->is_open);

        // 2. Venta en mesa: Bs. 30 en efectivo
        $table = Table::create(['branch_id' => $this->branch->id, 'name' => 'Mesa 1', 'status' => 'available']);
        $order1 = $this->orderService->createOrder($this->branch->id, $table->id, $this->user->id, null, 'dine_in');
        $this->orderService->addItem($order1, ['product_variant_id' => $this->variant->id, 'quantity' => 1]);
        $order1->refresh();
        $this->checkoutService->processPayment($order1, [['method' => 'cash', 'amount' => 30.00]]);

        // 3. Venta takeaway: Bs. 60 por QR
        $order2 = $this->orderService->createOrder($this->branch->id, null, $this->user->id, null, 'takeaway');
        $this->orderService->addItem($order2, ['product_variant_id' => $this->variant->id, 'quantity' => 2]);
        $order2->refresh();
        $this->checkoutService->processPayment($order2, [['method' => 'qr', 'amount' => 60.00]]);

        // 4. Venta delivery: Bs. 90 mixto (50 cash + 40 transfer)
        $order3 = $this->orderService->createOrder($this->branch->id, null, $this->user->id, null, 'delivery');
        $this->orderService->addItem($order3, ['product_variant_id' => $this->variant->id, 'quantity' => 3]);
        $order3->refresh();
        $this->checkoutService->processPayment($order3, [
            ['method' => 'cash', 'amount' => 50.00],
            ['method' => 'transfer', 'amount' => 40.00],
        ]);

        // 5. Verificar totales
        $session->refresh();
        // Efectivo esperado: 200 + 30 + 50 = 280
        $this->assertEquals(280.00, $session->calculateExpected());
        // QR: 60
        $this->assertEquals(60.00, $session->getTotalByPaymentMethod('qr'));
        // Transfer: 40
        $this->assertEquals(40.00, $session->getTotalByPaymentMethod('transfer'));

        // 6. Verificar que todas las órdenes están pagadas
        $this->assertEquals('paid', $order1->fresh()->status);
        $this->assertEquals('paid', $order2->fresh()->status);
        $this->assertEquals('paid', $order3->fresh()->status);

        // 7. Cerrar caja: cajero contó Bs. 275 (faltante de 5)
        $closed = $this->cashService->closeSession($session, $this->user->id, 275.00, 'Faltaron 5 Bs');
        $this->assertEquals(275.00, $closed->closing_amount);
        $this->assertEquals(280.00, $closed->expected_amount);
        $this->assertEquals(-5.00, $closed->difference);
        $this->assertFalse($closed->is_open);
        $this->assertNotNull($closed->closed_at);
    }
}
