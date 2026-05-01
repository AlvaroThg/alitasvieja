<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->enum('type', ['birthday', 'discount', 'combo', 'free_item', 'custom']);
            $table->enum('discount_type', ['percentage', 'fixed', 'free_item']);
            $table->decimal('discount_value', 10, 2)->default(0);
            $table->foreignId('free_product_variant_id')->nullable()->constrained('product_variants')->onDelete('set null');
            $table->integer('free_quantity')->default(1);
            $table->json('conditions')->nullable();
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['branch_id', 'is_active']);
            $table->index(['is_active', 'starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
