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
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
            $table->date('tanggal');
            $table->string('produk');
            $table->integer('jumlah_terjual');
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('biaya_per_unit', 15, 2)->default(0);
            $table->decimal('total_pendapatan', 15, 2)->nullable();
            $table->decimal('laba', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incomes');
    }
};
