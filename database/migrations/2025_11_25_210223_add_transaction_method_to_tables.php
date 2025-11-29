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
        // Add transaction_method to transactions table
        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('transaction_method', ['manual', 'rfid', 'barcode', 'voucher', 'system'])
                  ->default('manual')
                  ->after('type')
                  ->comment('Method: manual=input manual, rfid=RFID scanner, barcode=barcode scanner, voucher=voucher redeem, system=automated system');
        });

        // Add transaction_method to sales table
        Schema::table('sales', function (Blueprint $table) {
            $table->enum('transaction_method', ['manual', 'rfid', 'barcode', 'mixed'])
                  ->default('manual')
                  ->after('payment_method')
                  ->comment('Method: manual=manual input, rfid=RFID scan, barcode=barcode scan, mixed=combination');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('transaction_method');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('transaction_method');
        });
    }
};
