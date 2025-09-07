<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Income;
use App\Models\Expenditure;
use App\Models\Debt;
use App\Models\Receivable;
use App\Models\FixedCost;
use App\Models\Capital;
use App\Models\Capitalearly;
use App\Models\Product;
use App\Models\StockHistory;


class ClearDummyData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:clear-dummy {--force : Force deletion without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hapus semua data dummy yang dibuat melalui seeder';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('Apakah Anda yakin ingin menghapus SEMUA data dummy? Tindakan ini tidak dapat dibatalkan!')) {
                $this->info('Operasi dibatalkan.');
                return 0;
            }
        }

        $this->info('Memulai penghapusan data dummy...');

        try {
            // Hapus data dalam urutan yang benar untuk menghindari constraint violations
            $this->info('Menghapus StockHistory...');
            StockHistory::query()->delete();
            $this->info('✓ StockHistory berhasil dihapus');





            $this->info('Menghapus Income...');
            Income::query()->delete();
            $this->info('✓ Income berhasil dihapus');

            $this->info('Menghapus Expenditure...');
            Expenditure::query()->delete();
            $this->info('✓ Expenditure berhasil dihapus');

            $this->info('Menghapus FixedCost...');
            FixedCost::query()->delete();
            $this->info('✓ FixedCost berhasil dihapus');

            $this->info('Menghapus Debt...');
            Debt::query()->delete();
            $this->info('✓ Debt berhasil dihapus');

            $this->info('Menghapus Receivable...');
            Receivable::query()->delete();
            $this->info('✓ Receivable berhasil dihapus');

            $this->info('Menghapus Capital...');
            Capital::query()->delete();
            $this->info('✓ Capital berhasil dihapus');

            $this->info('Menghapus Capitalearly...');
            Capitalearly::query()->delete();
            $this->info('✓ Capitalearly berhasil dihapus');

            $this->info('Menghapus Product...');
            Product::query()->delete();
            $this->info('✓ Product berhasil dihapus');

            $this->info('Menghapus User...');
            User::query()->delete();
            $this->info('✓ User berhasil dihapus');

            $this->info('🎉 Semua data dummy berhasil dihapus!');
            $this->info('Database sekarang bersih dari data dummy.');

        } catch (\Exception $e) {
            $this->error('Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
