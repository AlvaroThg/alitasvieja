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
        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('coated_price', 8, 2)->default(0.00)->after('price');
        });

        // Inicializar coated_price a 5.00 para las variantes de productos de alitas existentes
        $wingProductIds = DB::table('products')->where('is_wings', true)->pluck('id');
        if ($wingProductIds->count() > 0) {
            DB::table('product_variants')
                ->whereIn('product_id', $wingProductIds)
                ->update(['coated_price' => 5.00]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('coated_price');
        });
    }
};
