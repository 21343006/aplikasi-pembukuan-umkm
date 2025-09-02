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
        Schema::create('stock_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['in', 'out', 'adjustment', 'initial']); // in: masuk, out: keluar, adjustment: penyesuaian, initial: stok awal
            $table->integer('quantity_change'); // perubahan jumlah (bisa positif atau negatif)
            $table->integer('quantity_before'); // jumlah sebelum perubahan
            $table->integer('quantity_after'); // jumlah setelah perubahan
            $table->text('description'); // keterangan perubahan
            $table->string('reference_type')->nullable(); // tipe referensi (income, manual, etc)
            $table->unsignedBigInteger('reference_id')->nullable(); // ID referensi
            $table->timestamps();
            
            $table->index(['product_id', 'created_at']);
            $table->index(['type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_histories');
    }
};
