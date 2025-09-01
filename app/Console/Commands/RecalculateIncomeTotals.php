<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Income;
class RecalculateIncomeTotals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:recalculate-income-totals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculates total_pendapatan and laba for all income records.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting recalculation of income totals...');

        $incomes = Income::all();
        $updatedCount = 0;

        foreach ($incomes as $income) {
            $oldTotalPendapatan = $income->total_pendapatan;
            $oldLaba = $income->laba;

            $newTotalPendapatan = (float) $income->harga_satuan * (int) $income->jumlah_terjual;
            $newLaba = ($income->harga_satuan - ($income->biaya_per_unit ?? 0)) * $income->jumlah_terjual;

            if ($income->total_pendapatan != $newTotalPendapatan || $income->laba != $newLaba) {
                $income->total_pendapatan = $newTotalPendapatan;
                $income->laba = $newLaba;
                $income->save();
                $updatedCount++;
            }
        }

        $this->info("Recalculation complete. {$updatedCount} records updated.");
    }
}