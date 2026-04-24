<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_wings')->default(false)->after('name');
            $table->boolean('tracks_stock')->default(false)->after('is_wings');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->integer('wings_count')->default(0)->after('name');
            $table->integer('max_sauces')->default(0)->after('wings_count');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_wings', 'tracks_stock']);
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['wings_count', 'max_sauces']);
        });
    }
};
