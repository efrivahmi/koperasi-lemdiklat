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
        // Add indexes to sales table for faster queries
        Schema::table('sales', function (Blueprint $table) {
            $table->index('created_at');
            $table->index('payment_method');
            $table->index('status');
            $table->index('student_id');
            $table->index('user_id');
            $table->index(['created_at', 'status']); // Composite for summary queries
            $table->index(['created_at', 'payment_method']); // Composite for payment filter
        });

        // Add indexes to transactions table
        Schema::table('transactions', function (Blueprint $table) {
            $table->index('student_id');
            $table->index('created_at');
            $table->index('type');
            $table->index(['student_id', 'created_at']); // Composite for student transactions
        });

        // Add indexes to students table
        Schema::table('students', function (Blueprint $table) {
            $table->index('nis');
            $table->index('user_id');
        });

        // Add indexes to products table
        Schema::table('products', function (Blueprint $table) {
            $table->index('barcode');
            $table->index('category_id');
        });

        // Add indexes to stock_adjustments table
        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->index('product_id');
            $table->index('adjusted_by');
            $table->index('created_at');
            $table->index(['created_at', 'type']); // Composite for reports
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove indexes from sales table
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['payment_method']);
            $table->dropIndex(['status']);
            $table->dropIndex(['student_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['created_at', 'status']);
            $table->dropIndex(['created_at', 'payment_method']);
        });

        // Remove indexes from transactions table
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['student_id']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['type']);
            $table->dropIndex(['student_id', 'created_at']);
        });

        // Remove indexes from students table
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex(['nis']);
            $table->dropIndex(['user_id']);
        });

        // Remove indexes from products table
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['barcode']);
            $table->dropIndex(['category_id']);
        });

        // Remove indexes from stock_adjustments table
        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
            $table->dropIndex(['adjusted_by']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['created_at', 'type']);
        });
    }
};
