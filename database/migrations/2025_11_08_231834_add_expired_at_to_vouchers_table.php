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
        // Add expired_at column
        Schema::table('vouchers', function (Blueprint $table) {
            $table->timestamp('expired_at')->nullable()->after('nominal');
        });

        // Update existing status values from 'tersedia'/'terpakai' to 'available'/'used'
        DB::statement("UPDATE vouchers SET status = 'available' WHERE status = 'tersedia'");
        DB::statement("UPDATE vouchers SET status = 'used' WHERE status = 'terpakai'");

        // Modify status enum to use English values
        DB::statement("ALTER TABLE vouchers MODIFY COLUMN status ENUM('available', 'used') DEFAULT 'available'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert status values back to Indonesian
        DB::statement("UPDATE vouchers SET status = 'tersedia' WHERE status = 'available'");
        DB::statement("UPDATE vouchers SET status = 'terpakai' WHERE status = 'used'");

        DB::statement("ALTER TABLE vouchers MODIFY COLUMN status ENUM('tersedia', 'terpakai') DEFAULT 'tersedia'");

        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn('expired_at');
        });
    }
};
