<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Saldo de Caja Chica (fondo de gastos menores) por sucursal.
        Schema::table('branches', function (Blueprint $table) {
            $table->decimal('petty_cash_balance', 10, 2)->default(0)->after('phone');
        });

        // Distingue a qué caja pertenece el movimiento:
        //   sales    = Caja de Venta (afecta el arqueo de la sesión)
        //   petty    = Caja Chica (no afecta la Caja de Venta)
        //   transfer = traspaso de Caja de Venta a Caja Chica
        Schema::table('cash_movements', function (Blueprint $table) {
            $table->string('cash_box', 20)->default('sales')->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn('petty_cash_balance');
        });
        Schema::table('cash_movements', function (Blueprint $table) {
            $table->dropColumn('cash_box');
        });
    }
};
