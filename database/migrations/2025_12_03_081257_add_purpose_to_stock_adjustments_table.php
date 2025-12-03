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
        Schema::table('stock_adjustments', function (Blueprint $table) {
            // Purpose of stock adjustment for financial calculation
            // - 'sale': For sales transaction (has revenue, calculate gross profit)
            // - 'internal_use': For office/company use (no revenue, pure cost/loss)
            // - 'personal_use': For personal use (no revenue, pure cost/loss)
            // - 'damage': Damaged goods (no revenue, pure cost/loss)
            // - 'expired': Expired goods (no revenue, pure cost/loss)
            // - 'return_to_supplier': Return to supplier (may have refund)
            // - 'other': Other reasons
            $table->enum('purpose', ['sale', 'internal_use', 'personal_use', 'damage', 'expired', 'return_to_supplier', 'other'])
                  ->default('other')
                  ->after('type')
                  ->comment('Purpose of adjustment for financial impact calculation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->dropColumn('purpose');
        });
    }
};
