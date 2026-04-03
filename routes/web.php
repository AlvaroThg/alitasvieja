<?php
// routes/web.php — estructura base de rutas con roles

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

// ─── Públicas ─────────────────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('login'));
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ─── POS — Cajeros y Mozos ────────────────────────────────────────────────────
Route::middleware(['auth', 'role:owner,cashier,waiter', 'branch'])->prefix('pos')->name('pos.')->group(function () {
    // Las rutas de POS se agregarán en la Fase 1
    Route::get('/', fn() => view('pos.index'))->name('index');
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
