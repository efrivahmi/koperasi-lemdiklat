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
        Schema::table('sales', function (Blueprint $table) {
            // Rename total_amount to total
            $table->renameColumn('total_amount', 'total');
        });

        // Modify enum after rename
        DB::statement("ALTER TABLE sales MODIFY COLUMN payment_method ENUM('cash', 'balance') NOT NULL");

        Schema::table('sales', function (Blueprint $table) {
            // Add new columns for cash payment
            $table->decimal('cash_amount', 10, 2)->nullable()->after('total');
            $table->decimal('change_amount', 10, 2)->nullable()->after('cash_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['cash_amount', 'change_amount']);
        });

        DB::statement("ALTER TABLE sales MODIFY COLUMN payment_method ENUM('tunai', 'saldo') NOT NULL");

        Schema::table('sales', function (Blueprint $table) {
            $table->renameColumn('total', 'total_amount');
        });
    }
};
