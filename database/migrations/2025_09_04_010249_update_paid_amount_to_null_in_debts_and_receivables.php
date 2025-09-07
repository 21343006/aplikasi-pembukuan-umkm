<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update debts table - set paid_amount to NULL where it's 0
        DB::table('debts')
            ->where('paid_amount', 0)
            ->update(['paid_amount' => null]);
        
        // Update receivables table - set paid_amount to NULL where it's 0
        DB::table('receivables')
            ->where('paid_amount', 0)
            ->update(['paid_amount' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('debts_and_receivables', function (Blueprint $table) {
            //
        });
    }
};
