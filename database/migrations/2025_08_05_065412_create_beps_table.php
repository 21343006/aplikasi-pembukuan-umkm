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
        Schema::create('beps', function (Blueprint $table) {
            $table->id();
            $table->string('nama_produk');
            $table->decimal('modal_tetap', 15, 2);
            $table->decimal('harga_per_barang', 15, 2);
            $table->decimal('modal_per_barang', 15, 2);
            $table->integer('bep')->nullable(); // Break Even Point (unit)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beps');
    }
};