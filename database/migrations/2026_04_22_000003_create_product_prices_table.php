<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla de precios por sucursal para product_variants.
 * La tabla product_variants ya tiene un campo 'price' (precio base/default),
 * pero esta tabla permite precios diferenciados por sucursal.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained('product_variants')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained('branches');
            $table->decimal('price', 10, 2);
            $table->timestamps();

            $table->unique(['product_variant_id', 'branch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_prices');
    }
};
