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
        // Alter role enum to include 'master'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('master', 'admin', 'kasir', 'siswa') NOT NULL DEFAULT 'siswa'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum without 'master'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'kasir', 'siswa') NOT NULL DEFAULT 'siswa'");
    }
};
