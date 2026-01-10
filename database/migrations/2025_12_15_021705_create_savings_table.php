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
        Schema::create('savings', function (Blueprint $table) {
            $table->id();

            // Polymorphic relation to Student or Teacher
            $table->string('saver_type'); // App\Models\Student or App\Models\Teacher
            $table->unsignedBigInteger('saver_id');
            $table->index(['saver_type', 'saver_id']);

            // Transaction details
            $table->enum('type', ['deposit', 'withdrawal']); // Setoran atau Penarikan
            $table->decimal('amount', 10, 2); // Jumlah transaksi
            $table->decimal('balance_before', 10, 2); // Saldo sebelum transaksi
            $table->decimal('balance_after', 10, 2); // Saldo setelah transaksi
            $table->text('description')->nullable(); // Keterangan

            // Transaction metadata
            $table->timestamp('transaction_date'); // Tanggal transaksi
            $table->index('transaction_date'); // Index untuk query performance

            // Audit trail
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null'); // User yang memproses
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
        Schema::dropIfExists('savings');
    }
};
