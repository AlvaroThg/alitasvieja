<?php
// routes/web.php — estructura base de rutas con roles

use App\Http\Controllers\Auth\AuthController;
use App\Modules\Auth\Http\Controllers\Admin\UserController;
use App\Modules\Cash\Http\Controllers\CashController;
use App\Modules\Cash\Http\Controllers\CashReportController;
use App\Modules\Inventory\Http\Controllers\InventoryMovementController;
use App\Modules\Menu\Http\Controllers\Admin\PriceController;
use App\Modules\Orders\Http\Controllers\OrderController;
use App\Modules\Orders\Http\Controllers\CheckoutController;
use App\Modules\Orders\Http\Controllers\KitchenController;
use App\Modules\Reports\Http\Controllers\AdminReportController;
use App\Modules\Tickets\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

// ─── Públicas ─────────────────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('login'));
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ─── POS — Cajeros y Mozos (Fase 1) ──────────────────────────────────────────
Route::middleware(['auth', 'role:branch_admin,cashier,waiter', 'branch'])->prefix('pos')->name('pos.')->group(function () {
    Route::get('/', fn() => view('pos.index'))->name('index');

    // Pedidos
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/items', [OrderController::class, 'addItem'])->name('orders.addItem');
    Route::delete('/orders/{order}/items/{item}', [OrderController::class, 'removeItem'])->name('orders.removeItem');

    // Checkout
    Route::get('/checkout/{order}', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout/{order}/pay', [CheckoutController::class, 'process'])->name('checkout.process');

    // Tickets
    Route::get('/tickets/{order}/kitchen', [TicketController::class, 'kitchen'])->name('tickets.kitchen');
    Route::get('/tickets/{order}/cashier', [TicketController::class, 'cashier'])->name('tickets.cashier');
});

// ─── Cocina — Vista de pedidos activos ────────────────────────────────────────
Route::middleware(['auth', 'role:owner,cashier,waiter', 'branch'])->prefix('kitchen')->name('kitchen.')->group(function () {
    Route::get('/orders', [KitchenController::class, 'index'])->name('orders.index');
    Route::patch('/orders/{order}/ready', [KitchenController::class, 'markReady'])->name('orders.ready');
});

// ─── Caja — Cajero y Branch Admin (Fase 2) ───────────────────────────────────
Route::middleware(['auth', 'role:branch_admin,cashier', 'branch'])->prefix('cash')->name('cash.')->group(function () {
    Route::get('/sessions/active', [CashController::class, 'active'])->name('sessions.active');
    Route::post('/sessions/open', [CashController::class, 'open'])->name('sessions.open');
    Route::get('/sessions/{session}', [CashController::class, 'show'])->name('sessions.show');
    Route::post('/sessions/{session}/movements', [CashController::class, 'addMovement'])->name('sessions.addMovement');
    Route::post('/sessions/{session}/close', [CashController::class, 'close'])->name('sessions.close');
    Route::get('/movements', function () { return view('cash.index'); })->name('movements');
});

// ─── Admin — Solo Owner ──────────────────────────────────────────────────────
Route::middleware(['auth', 'role:owner'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard con KPIs
    Route::get('/dashboard', [AdminReportController::class, 'dashboard'])->name('dashboard');

    // NUEVO — Fase 4: Dashboard API con períodos
    Route::get('/dashboard/data', [AdminReportController::class, 'dashboardData'])->name('dashboard.data');

    // Selector de sucursal activa
    Route::post('/branch/switch', [AuthController::class, 'switchBranch'])->name('branch.switch');

    // Gestión de Productos
    Route::get('/products', function () { return view('admin.products'); })->name('products.index');

    // Precios por sucursal (Fase 2)
    Route::get('/prices', [PriceController::class, 'index'])->name('prices.index');
    Route::put('/prices/bulk', [PriceController::class, 'bulkUpdate'])->name('prices.bulkUpdate');
    Route::put('/prices/{variant}/branch/{branch}', [PriceController::class, 'update'])->name('prices.update');

    // Gestión de Categorías
    Route::get('/categories', function () { return view('admin.categories'); })->name('categories.index');

    // Gestión de Salsas
    Route::get('/sauces', function () { return view('admin.sauces'); })->name('sauces.index');

    // Usuarios y roles (Migrado a Livewire)
    Route::get('/users', function () { return view('admin.users'); })->name('users.index');

    // Gestión de Promociones (Fase 3)
    Route::get('/promotions', function () { return view('admin.promotions'); })->name('promotions.index');

    // NUEVO — Fase 4: Reportes solo owner
    Route::get('/reports/sales', [AdminReportController::class, 'salesByPeriod'])->name('reports.sales');
    Route::get('/reports/promotions', [AdminReportController::class, 'promotionStats'])->name('reports.promotions');
});

// Rutas admin accesibles por owner Y branch_admin
Route::middleware(['auth', 'role:owner,branch_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/orders', [AdminReportController::class, 'orders'])->name('orders.index');

    // Historial de Movimientos de Inventario + reporte PDF
    Route::get('/inventory/movements', fn () => view('admin.inventory-movements'))->name('inventory.movements');
    Route::get('/inventory/movements/export', [InventoryMovementController::class, 'export'])->name('inventory.movements.export');

    // NUEVO — Fase 4: Reportes owner + branch_admin
    Route::get('/reports/payments', [AdminReportController::class, 'paymentMethods'])->name('reports.payments');
    Route::get('/reports/wings', [AdminReportController::class, 'wingStats'])->name('reports.wings');

    // NOTA: /export se define ANTES de /{session} para que Laravel no
    // interprete "export" como un ID de CashSession (route model binding).
    Route::get('/reports/cash', [CashReportController::class, 'index'])->name('reports.cash.index');
    Route::get('/reports/cash/export', [CashReportController::class, 'export'])->name('reports.cash.export');
    Route::get('/reports/cash/{session}', [CashReportController::class, 'show'])->name('reports.cash.show');
});

// ─── Inventario — Accesible por Cajeros, Branch Admin y Owner ───────────────
Route::middleware(['auth', 'role:owner,branch_admin,cashier', 'branch'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/inventory', function () { return view('admin.inventory'); })->name('inventory.index');
});

// ─── POS — Aplicar Promociones en Pedidos (Fase 3) ─────────────────────────
Route::middleware(['auth', 'role:branch_admin,cashier,waiter', 'branch'])->prefix('pos')->name('pos.')->group(function () {
    Route::get('/orders/{order}/promotions', [\App\Modules\Promotions\Http\Controllers\PromotionController::class, 'available'])->name('orders.promotions.available');
    Route::post('/orders/{order}/promotions', [\App\Modules\Promotions\Http\Controllers\PromotionController::class, 'applyToOrder'])->name('orders.promotions.apply');
    Route::delete('/orders/{order}/promotions', [\App\Modules\Promotions\Http\Controllers\PromotionController::class, 'removeFromOrder'])->name('orders.promotions.remove');
});