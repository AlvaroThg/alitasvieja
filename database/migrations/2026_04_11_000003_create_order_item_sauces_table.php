<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_item_sauces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained('order_items')->onDelete('cascade');
            $table->foreignId('sauce_id')->constrained('sauces');
            $table->integer('quantity')->default(0);
            // is_coated: true = alitas bañadas en esta salsa; false = salsa servida aparte
            $table->boolean('is_coated')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_item_sauces');
    }
};
