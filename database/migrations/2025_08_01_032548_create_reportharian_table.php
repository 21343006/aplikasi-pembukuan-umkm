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
        Schema::create('reportharian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('tanggal');
            $table->string('keterangan')->nullable();
            $table->decimal('uang_masuk', 15, 2)->default(0);
            $table->decimal('uang_keluar', 15, 2)->default(0);
            $table->decimal('saldo', 15, 2)->default(0);
            $table->timestamps();

            // Index untuk performa query
            $table->index(['user_id', 'tanggal']);
            $table->index(['user_id', 'tanggal', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reportharian');
    }
};