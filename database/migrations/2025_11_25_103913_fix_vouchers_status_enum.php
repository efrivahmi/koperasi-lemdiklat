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
        // Update existing data first
        DB::statement("UPDATE vouchers SET status = 'available' WHERE status = 'tersedia'");
        DB::statement("UPDATE vouchers SET status = 'used' WHERE status = 'terpakai'");

        // Change enum column
        DB::statement("ALTER TABLE vouchers MODIFY COLUMN status ENUM('available', 'used', 'expired') NOT NULL DEFAULT 'available'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert data
        DB::statement("UPDATE vouchers SET status = 'tersedia' WHERE status = 'available'");
        DB::statement("UPDATE vouchers SET status = 'terpakai' WHERE status = 'used'");

        // Revert enum column
        DB::statement("ALTER TABLE vouchers MODIFY COLUMN status ENUM('tersedia', 'terpakai') NOT NULL DEFAULT 'tersedia'");
    }
};
