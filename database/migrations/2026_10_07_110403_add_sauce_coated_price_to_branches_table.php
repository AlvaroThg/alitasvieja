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
        Schema::table('branches', function (Blueprint $table) {
            $table->decimal('sauce_coated_price', 8, 2)->default(0.00)->after('petty_cash_balance');
        });

        // Set default 5.00 for Cochabamba
        \Illuminate\Support\Facades\DB::table('branches')
            ->where('slug', 'cbba')
            ->orWhere('city', 'Cochabamba')
            ->orWhere('name', 'like', '%Cochabamba%')
            ->update(['sauce_coated_price' => 5.00]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn('sauce_coated_price');
        });
    }
};
