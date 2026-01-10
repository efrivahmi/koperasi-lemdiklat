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
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nip', 50)->unique(); // Nomor Induk Pegawai
            $table->string('nuptk', 50)->unique()->nullable(); // Nomor Unik Pendidik & Tenaga Kependidikan
            $table->string('name');
            $table->string('jabatan')->nullable(); // Guru Mapel, Wali Kelas, Kepala Sekolah, dll
            $table->string('mata_pelajaran')->nullable(); // Mata pelajaran yang diajar
            $table->enum('jenjang', ['SMA Taruna Nusantara Indonesia', 'SMK Taruna Nusantara Jaya'])->nullable();
            $table->text('alamat_lengkap')->nullable();
            $table->decimal('balance', 10, 2)->default(0); // Saldo untuk transaksi
            $table->string('rfid_uid', 50)->unique()->nullable(); // UID kartu RFID
            $table->string('foto')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
