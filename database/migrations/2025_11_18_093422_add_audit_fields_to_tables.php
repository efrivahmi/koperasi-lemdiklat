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
        // Add audit fields to products
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('image_path')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('products', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->onDelete('set null');
            }
        });

        // Add audit fields to sales
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->after('change_amount')->constrained('users')->onDelete('set null');
            }
        });

        // Add audit fields to stock_ins
        Schema::table('stock_ins', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_ins', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->after('user_id')->constrained('users')->onDelete('set null');
            }
        });

        // Add audit fields to expenses
        Schema::table('expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('expenses', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('expenses', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            }
        });

        // Add audit fields to students
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('balance')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('students', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->onDelete('set null');
            }
        });

        // Add audit fields to categories
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('description')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('categories', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['created_by', 'updated_by']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['updated_by']);
            $table->dropColumn('updated_by');
        });

        Schema::table('stock_ins', function (Blueprint $table) {
            $table->dropForeign(['updated_by']);
            $table->dropColumn('updated_by');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['created_by', 'updated_by']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['created_by', 'updated_by']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['created_by', 'updated_by']);
        });
    }
};
