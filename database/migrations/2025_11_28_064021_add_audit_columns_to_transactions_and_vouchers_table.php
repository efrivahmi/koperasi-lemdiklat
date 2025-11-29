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
        // Add audit columns to transactions table
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('reference_id')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('transactions', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->onDelete('set null');
            }
        });

        // Add audit columns to vouchers table
        Schema::table('vouchers', function (Blueprint $table) {
            if (!Schema::hasColumn('vouchers', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('used_at')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('vouchers', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove audit columns from transactions table
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'updated_by')) {
                $table->dropForeign(['updated_by']);
                $table->dropColumn('updated_by');
            }
            if (Schema::hasColumn('transactions', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
        });

        // Remove audit columns from vouchers table
        Schema::table('vouchers', function (Blueprint $table) {
            if (Schema::hasColumn('vouchers', 'updated_by')) {
                $table->dropForeign(['updated_by']);
                $table->dropColumn('updated_by');
            }
            if (Schema::hasColumn('vouchers', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
        });
    }
};
