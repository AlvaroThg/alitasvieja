<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('table_id')->nullable()->constrained('tables');
            $table->foreignId('user_id')->constrained('users');
            $table->string('order_number', 20)->unique();
            $table->enum('status', ['open', 'ready', 'paid', 'cancelled'])->default('open');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->enum('payment_method', ['cash', 'card', 'qr', 'transfer', 'mixed'])->nullable();
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            // Índices compuestos para consultas frecuentes
            $table->index(['branch_id', 'status']);
            $table->index(['branch_id', 'opened_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
