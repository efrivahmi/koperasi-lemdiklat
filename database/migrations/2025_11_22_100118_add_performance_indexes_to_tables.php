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
        // Add indexes to students table for fast searching
        Schema::table('students', function (Blueprint $table) {
            $table->index('nis', 'idx_students_nis');
            $table->index('rfid_uid', 'idx_students_rfid');
            $table->index('name', 'idx_students_name');
        });

        // Add indexes to products table for barcode scanning
        Schema::table('products', function (Blueprint $table) {
            $table->index('barcode', 'idx_products_barcode');
            $table->index('category_id', 'idx_products_category');
            $table->index('stock', 'idx_products_stock');
        });

        // Add indexes to sales table for reporting
        Schema::table('sales', function (Blueprint $table) {
            $table->index('student_id', 'idx_sales_student');
            $table->index('user_id', 'idx_sales_user');
            $table->index('created_at', 'idx_sales_created');
            $table->index('status', 'idx_sales_status');
        });

        // Add indexes to sale_items for product reports
        Schema::table('sale_items', function (Blueprint $table) {
            $table->index('product_id', 'idx_sale_items_product');
            $table->index('sale_id', 'idx_sale_items_sale');
        });

        // Add indexes to transactions for student history
        Schema::table('transactions', function (Blueprint $table) {
            $table->index('student_id', 'idx_transactions_student');
            $table->index('type', 'idx_transactions_type');
            $table->index('created_at', 'idx_transactions_created');
        });

        // Add indexes to stock_ins for inventory tracking
        Schema::table('stock_ins', function (Blueprint $table) {
            $table->index('product_id', 'idx_stock_ins_product');
            $table->index('created_at', 'idx_stock_ins_created');
        });

        // Add indexes to vouchers
        Schema::table('vouchers', function (Blueprint $table) {
            $table->index('code', 'idx_vouchers_code');
            $table->index('status', 'idx_vouchers_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('idx_students_nis');
            $table->dropIndex('idx_students_rfid');
            $table->dropIndex('idx_students_name');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_barcode');
            $table->dropIndex('idx_products_category');
            $table->dropIndex('idx_products_stock');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('idx_sales_student');
            $table->dropIndex('idx_sales_user');
            $table->dropIndex('idx_sales_created');
            $table->dropIndex('idx_sales_status');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropIndex('idx_sale_items_product');
            $table->dropIndex('idx_sale_items_sale');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('idx_transactions_student');
            $table->dropIndex('idx_transactions_type');
            $table->dropIndex('idx_transactions_created');
        });

        Schema::table('stock_ins', function (Blueprint $table) {
            $table->dropIndex('idx_stock_ins_product');
            $table->dropIndex('idx_stock_ins_created');
        });

        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropIndex('idx_vouchers_code');
            $table->dropIndex('idx_vouchers_status');
        });
    }
};
