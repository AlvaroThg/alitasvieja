<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('promotion_id')->constrained('promotions')->onDelete('cascade');
            $table->decimal('discount_applied', 10, 2);
            $table->foreignId('free_item_variant_id')->nullable()->constrained('product_variants')->onDelete('set null');
            $table->integer('free_item_quantity')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['order_id', 'promotion_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_promotions');
    }
};
