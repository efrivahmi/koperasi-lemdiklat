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
        Schema::table('transactions', function (Blueprint $table) {
            // Add polymorphic relation columns for buyer (student or teacher)
            $table->string('buyer_type')->nullable()->after('id');
            $table->unsignedBigInteger('buyer_id')->nullable()->after('buyer_type');

            // Add index for polymorphic relation
            $table->index(['buyer_type', 'buyer_id']);

            // Make student_id nullable for backward compatibility
            $table->foreignId('student_id')->nullable()->change();
        });

        // Migrate existing data from student_id to polymorphic relation
        DB::statement("
            UPDATE transactions
            SET buyer_type = 'App\\\\Models\\\\Student',
                buyer_id = student_id
            WHERE student_id IS NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Drop polymorphic columns
            $table->dropIndex(['buyer_type', 'buyer_id']);
            $table->dropColumn(['buyer_type', 'buyer_id']);
        });
    }
};
