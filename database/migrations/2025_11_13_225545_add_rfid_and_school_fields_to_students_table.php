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
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'rfid_uid')) {
                $table->string('rfid_uid', 50)->nullable()->unique()->after('nis');
            }
            if (!Schema::hasColumn('students', 'nisn')) {
                $table->string('nisn', 20)->nullable()->after('rfid_uid');
            }
            if (!Schema::hasColumn('students', 'jenjang')) {
                $table->enum('jenjang', ['SMA Taruna Nusantara Indonesia', 'SMK Taruna Nusantara Jaya'])->nullable()->after('kelas');
            }
            if (!Schema::hasColumn('students', 'alamat_lengkap')) {
                $table->text('alamat_lengkap')->nullable()->after('jenjang');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['rfid_uid', 'nisn', 'jenjang', 'alamat_lengkap']);
        });
    }
};
