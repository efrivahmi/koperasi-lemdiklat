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
        // Update existing values if any
        DB::statement("UPDATE transactions SET type = 'topup' WHERE type = 'top-up'");
        DB::statement("UPDATE transactions SET type = 'purchase' WHERE type = 'pembelian'");
        DB::statement("UPDATE transactions SET type = 'return' WHERE type = 'pengembalian'");

        // Modify enum to use English values
        DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM('topup', 'debit', 'purchase', 'redeem', 'return')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to Indonesian values
        DB::statement("UPDATE transactions SET type = 'top-up' WHERE type = 'topup'");
        DB::statement("UPDATE transactions SET type = 'pembelian' WHERE type = 'purchase'");
        DB::statement("UPDATE transactions SET type = 'pengembalian' WHERE type = 'return'");

        DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM('top-up', 'pembelian', 'pengembalian')");
    }
};
