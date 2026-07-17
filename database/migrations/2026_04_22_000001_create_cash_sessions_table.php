<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('opened_by')->constrained('users');
            $table->foreignId('closed_by')->nullable()->constrained('users');
            $table->decimal('opening_amount', 10, 2);
            $table->decimal('closing_amount', 10, 2)->nullable();
            $table->decimal('expected_amount', 10, 2)->nullable();
            $table->decimal('difference', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            // Índices para consultas frecuentes
            $table->index(['branch_id', 'opened_at']);
            $table->index(['branch_id', 'closed_at']);

            // RESTRICCIÓN: solo una sesión abierta por branch.
            // MySQL no soporta índices parciales (WHERE), así que la unicidad
            // se garantiza en CashService con validación explícita + lockForUpdate.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_sessions');
    }
};
