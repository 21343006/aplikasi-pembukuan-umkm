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
        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('creditor_name'); // Nama kreditur/pemasok
            $table->text('description'); // Deskripsi utang
            $table->decimal('amount', 15, 2); // Jumlah utang
            $table->date('due_date'); // Tanggal jatuh tempo
            $table->enum('status', ['unpaid', 'partial', 'paid'])->default('unpaid'); // Status pembayaran
            $table->decimal('paid_amount', 15, 2)->default(0); // Jumlah yang sudah dibayar
            $table->date('paid_date')->nullable(); // Tanggal pembayaran
            $table->text('notes')->nullable(); // Catatan tambahan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debts');
    }
};
