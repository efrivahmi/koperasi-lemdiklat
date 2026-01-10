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
        // Check if columns exist, if not add them
        if (!Schema::hasColumn('sales', 'created_by')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->foreignId('created_by')->nullable()->after('user_id');
            });
        }

        if (!Schema::hasColumn('sales', 'updated_by')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->foreignId('updated_by')->nullable()->after('created_by');
            });
        }

        // Check if foreign keys exist, if not add them
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'sales'
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ");

        $existingKeys = collect($foreignKeys)->pluck('CONSTRAINT_NAME')->toArray();

        if (!in_array('sales_created_by_foreign', $existingKeys)) {
            Schema::table('sales', function (Blueprint $table) {
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            });
        }

        if (!in_array('sales_updated_by_foreign', $existingKeys)) {
            Schema::table('sales', function (Blueprint $table) {
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['created_by', 'updated_by']);
        });
    }
};
