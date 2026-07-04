<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Correlativo diario por sucursal (1, 2, 3… reinicia cada día)
            $table->smallInteger('daily_number')->unsigned()->default(0)->after('order_number');

            // Índice compuesto para garantizar unicidad del daily_number por branch y día.
            // NOTA: MySQL no soporta índice único con función DATE() directamente.
            // La unicidad se garantiza con lockForUpdate() en generateOrderNumber().
            // Este índice sirve para consultas rápidas de pedidos del día por sucursal.
            $table->index(['branch_id', 'opened_at', 'daily_number'], 'orders_branch_day_daily_idx');

            // Índice para consultas de reportes por status + closed_at (OBS 2)
            $table->index(['status', 'closed_at'], 'orders_status_closed_idx');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_branch_day_daily_idx');
            $table->dropIndex('orders_status_closed_idx');
            $table->dropColumn('daily_number');
        });
    }
};
