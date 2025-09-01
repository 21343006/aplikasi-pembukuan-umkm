<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            if (!Schema::hasColumn('incomes', 'biaya_per_unit')) {
                $table->decimal('biaya_per_unit', 15, 2)->default(0)->after('harga_satuan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            if (Schema::hasColumn('incomes', 'biaya_per_unit')) {
                $table->dropColumn('biaya_per_unit');
            }
        });
    }
};


