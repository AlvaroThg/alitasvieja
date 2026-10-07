<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_prices', function (Blueprint $table) {
            $table->decimal('coated_price', 8, 2)->nullable()->after('price');
        });

        // Inicializar coated_price en product_prices para Cochabamba si la variante tiene recargo o es alitas
        $cbbaBranch = DB::table('branches')->where('slug', 'cbba')->orWhere('city', 'Cochabamba')->first();
        if ($cbbaBranch) {
            $wingVariantIds = DB::table('product_variants')
                ->join('products', 'products.id', '=', 'product_variants.product_id')
                ->where('products.is_wings', true)
                ->pluck('product_variants.id');

            DB::table('product_prices')
                ->where('branch_id', $cbbaBranch->id)
                ->whereIn('product_variant_id', $wingVariantIds)
                ->update(['coated_price' => 5.00]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_prices', function (Blueprint $table) {
            $table->dropColumn('coated_price');
        });
    }
};
