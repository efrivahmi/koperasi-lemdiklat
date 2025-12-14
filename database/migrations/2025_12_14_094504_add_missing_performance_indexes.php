<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Check if an index exists on a table
     */
    private function indexExists($table, $indexName): bool
    {
        $database = DB::getDatabaseName();

        $result = DB::select(
            "SELECT COUNT(*) as count FROM information_schema.statistics
             WHERE table_schema = ? AND table_name = ? AND index_name = ?",
            [$database, $table, $indexName]
        );

        return $result[0]->count > 0;
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add index to students table for RFID lookups (if not exists)
        if (!$this->indexExists('students', 'idx_students_rfid_uid')) {
            Schema::table('students', function (Blueprint $table) {
                $table->index('rfid_uid', 'idx_students_rfid_uid');
            });
        }

        // Note: idx_products_barcode already exists, skip it

        // Add indexes to sales table for reporting
        if (!$this->indexExists('sales', 'idx_sales_created_at')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->index('created_at', 'idx_sales_created_at');
            });
        }

        if (!$this->indexExists('sales', 'idx_sales_status_created_at')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->index(['status', 'created_at'], 'idx_sales_status_created_at');
            });
        }

        // Add indexes to transactions table for student history
        if (!$this->indexExists('transactions', 'idx_transactions_student_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->index('student_id', 'idx_transactions_student_id');
            });
        }

        if (!$this->indexExists('transactions', 'idx_transactions_created_at')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->index('created_at', 'idx_transactions_created_at');
            });
        }

        if (!$this->indexExists('transactions', 'idx_transactions_student_created')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->index(['student_id', 'created_at'], 'idx_transactions_student_created');
            });
        }

        // Add index to sale_items for product sales analysis
        if (!$this->indexExists('sale_items', 'idx_sale_items_product_id')) {
            Schema::table('sale_items', function (Blueprint $table) {
                $table->index('product_id', 'idx_sale_items_product_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if ($this->indexExists('students', 'idx_students_rfid_uid')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropIndex('idx_students_rfid_uid');
            });
        }

        if ($this->indexExists('sales', 'idx_sales_created_at')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropIndex('idx_sales_created_at');
            });
        }

        if ($this->indexExists('sales', 'idx_sales_status_created_at')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropIndex('idx_sales_status_created_at');
            });
        }

        if ($this->indexExists('transactions', 'idx_transactions_student_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropIndex('idx_transactions_student_id');
            });
        }

        if ($this->indexExists('transactions', 'idx_transactions_created_at')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropIndex('idx_transactions_created_at');
            });
        }

        if ($this->indexExists('transactions', 'idx_transactions_student_created')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropIndex('idx_transactions_student_created');
            });
        }

        if ($this->indexExists('sale_items', 'idx_sale_items_product_id')) {
            Schema::table('sale_items', function (Blueprint $table) {
                $table->dropIndex('idx_sale_items_product_id');
            });
        }
    }
};
