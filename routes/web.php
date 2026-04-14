<?php
// routes/web.php — estructura base de rutas con roles

use App\Http\Controllers\Auth\AuthController;
use App\Modules\Orders\Http\Controllers\OrderController;
use App\Modules\Orders\Http\Controllers\CheckoutController;
use App\Modules\Tickets\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

// ─── Públicas ─────────────────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('login'));
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ─── POS — Cajeros y Mozos (Fase 1) ──────────────────────────────────────────
Route::middleware(['auth', 'role:owner,cashier,waiter', 'branch'])->prefix('pos')->name('pos.')->group(function () {
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
});

// ─── Caja — Cajero, Branch Admin, Owner ───────────────────────────────────────
Route::middleware(['auth', 'role:owner,branch_admin,cashier', 'branch'])->prefix('caja')->name('cash.')->group(function () {
    // Las rutas de caja se agregarán en la Fase 2
    Route::get('/', fn() => view('cash.index'))->name('index');
});

// ─── Admin — Solo Owner ───────────────────────────────────────────────────────
Route::middleware(['auth', 'role:owner'])->prefix('admin')->name('admin.')->group(function () {
    // Las rutas de administración se agregarán en fases posteriores
    Route::get('/dashboard', fn() => view('admin.dashboard'))->name('dashboard');

    // Selector de sucursal activa (para el owner)
    Route::post('/branch/switch', [AuthController::class, 'switchBranch'])->name('branch.switch');
});
