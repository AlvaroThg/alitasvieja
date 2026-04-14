<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('product_variant_id')->constrained('product_variants');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);           // snapshot del precio al registrar
            $table->decimal('extra_sauce_charge', 10, 2)->default(0); // cargo extra por salsas
            $table->decimal('subtotal', 10, 2);              // (unit_price × quantity) + extra_sauce_charge
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
