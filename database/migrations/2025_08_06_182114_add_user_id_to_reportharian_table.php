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
        // Check if user_id column doesn't exist and add it
        if (!Schema::hasColumn('reportharian', 'user_id')) {
            Schema::table('reportharian', function (Blueprint $table) {
                $table->foreignId('user_id')->after('id')->constrained()->onDelete('cascade');
                
                // Add indexes for better performance
                $table->index(['user_id', 'tanggal']);
                $table->index(['user_id', 'tanggal', 'id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('reportharian', 'user_id')) {
            Schema::table('reportharian', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropIndex(['user_id', 'tanggal']);
                $table->dropIndex(['user_id', 'tanggal', 'id']);
                $table->dropColumn('user_id');
            });
        }
    }
};