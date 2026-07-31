<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Registro de tickets generados (cocina / caja).
 *
 * TicketService ya escribía en esta tabla, pero la migración nunca se creó:
 * al pedir el ticket de cocina la app fallaba con "Table 'tickets' doesn't exist".
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches');

            $table->string('type', 20);            // kitchen | cashier
            $table->string('ticket_number');       // TK-<order_number>
            $table->string('pdf_path');            // ruta en storage
            $table->timestamp('printed_at')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // TicketService hace updateOrInsert por (order_id, type): un ticket
            // de cada tipo por pedido, se regenera en vez de duplicarse.
            $table->unique(['order_id', 'type']);
            $table->index('branch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
