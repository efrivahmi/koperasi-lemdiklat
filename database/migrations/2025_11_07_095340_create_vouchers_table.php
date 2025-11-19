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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->decimal('nominal', 10, 2);
            $table->enum('status', ['tersedia', 'terpakai'])->default('tersedia');
            $table->foreignId('student_id')->nullable()->constrained()->onDelete('set null'); // Siswa yang redeem
            $table->foreignId('used_by')->nullable()->constrained('users')->onDelete('set null'); // Kasir/siswa yang redeem
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
