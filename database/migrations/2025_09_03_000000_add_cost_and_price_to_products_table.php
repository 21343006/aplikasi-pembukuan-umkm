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
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'cost_per_unit')) {
                $table->decimal('cost_per_unit', 15, 2)->default(0)->after('low_stock_threshold');
            }
            if (!Schema::hasColumn('products', 'selling_price')) {
                $table->decimal('selling_price', 15, 2)->default(0)->after('cost_per_unit');
            }
            if (!Schema::hasColumn('products', 'unit')) {
                $table->string('unit', 50)->default('unit')->after('selling_price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['cost_per_unit', 'selling_price', 'unit']);
        });
    }
};
