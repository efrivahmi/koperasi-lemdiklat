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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['top-up', 'pembelian', 'pengembalian']);
            $table->decimal('amount', 10, 2);
            $table->decimal('ending_balance', 10, 2); // Saldo akhir setelah transaksi
            $table->text('description')->nullable();
            $table->string('reference_type')->nullable(); // sales, vouchers
            $table->unsignedBigInteger('reference_id')->nullable(); // ID dari reference_type
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
