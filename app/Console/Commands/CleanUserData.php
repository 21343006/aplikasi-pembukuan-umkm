<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Income;
use App\Models\Expenditure;
use App\Models\Product;
use App\Models\Capital;
use App\Models\Capitalearly;
use App\Models\FixedCost;
use App\Models\Debt;
use App\Models\Receivable;

use App\Models\StockHistory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanUserData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:clean-user {--user-id= : ID user tertentu} {--dry-run : Hanya tampilkan data yang akan dibersihkan tanpa menghapus}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membersihkan data yang tidak sesuai user dan memastikan isolasi data antar user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->option('user-id');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 Mode DRY RUN - Data tidak akan dihapus');
        }

        try {
            if ($userId) {
                // Clean untuk user tertentu
                $this->cleanForUser($userId, $dryRun);
            } else {
                // Clean untuk semua user
                $users = User::all();
                $this->info("📊 Membersihkan data untuk {$users->count()} user...");
                
                foreach ($users as $user) {
                    $this->cleanForUser($user->id, $dryRun);
                }
            }

            $this->info('✅ Pembersihan data selesai!');
            
        } catch (\Exception $e) {
            $this->error('❌ Terjadi kesalahan: ' . $e->getMessage());
            Log::error('Error in CleanUserData command: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function cleanForUser($userId, $dryRun = false)
    {
        $user = User::find($userId);
        if (!$user) {
            $this->warn("⚠️  User dengan ID {$userId} tidak ditemukan");
            return;
        }

        $this->info("👤 Membersihkan data untuk user: {$user->name} ({$user->email})");

        try {
            DB::beginTransaction();

            // 1. Hapus data yang tidak memiliki user_id
            $this->cleanOrphanedData($userId, $dryRun);

            // 2. Hapus data yang memiliki user_id yang tidak valid
            $this->cleanInvalidUserData($userId, $dryRun);

            // 3. Hapus data duplikat
            $this->cleanDuplicateData($userId, $dryRun);

            // 4. Validasi relasi antar tabel
            $this->validateRelations($userId, $dryRun);

            if (!$dryRun) {
                DB::commit();
                $this->info("   ✅ Data berhasil dibersihkan untuk user {$user->name}");
            } else {
                DB::rollBack();
                $this->info("   🔍 Mode DRY RUN - Data tidak diubah");
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("   ❌ Error: " . $e->getMessage());
            Log::error("Error cleaning data for user {$userId}: " . $e->getMessage());
        }
    }

    private function cleanOrphanedData($userId, $dryRun = false)
    {
        $this->info("   🧹 Membersihkan data orphaned...");

        // Hapus data yang tidak memiliki user_id
        $tables = ['incomes', 'expenditures', 'products', 'capitals', 'capitalearlys', 'fixed_costs', 'debts', 'receivables', 'stock_histories'];
        
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->whereNull('user_id')->count();
                if ($count > 0) {
                    if ($dryRun) {
                        $this->line("     🗑️  Akan dihapus {$count} data orphaned dari tabel {$table}");
                    } else {
                        DB::table($table)->whereNull('user_id')->delete();
                        $this->line("     ✅ Dihapus {$count} data orphaned dari tabel {$table}");
                    }
                }
            }
        }
    }

    private function cleanInvalidUserData($userId, $dryRun = false)
    {
        $this->info("   🚫 Membersihkan data dengan user_id tidak valid...");

        // Hapus data yang memiliki user_id yang tidak ada di tabel users
        $tables = ['incomes', 'expenditures', 'products', 'capitals', 'capitalearlys', 'fixed_costs', 'debts', 'receivables', 'stock_histories'];
        
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)
                    ->whereNotIn('user_id', User::pluck('id'))
                    ->count();
                
                if ($count > 0) {
                    if ($dryRun) {
                        $this->line("     🗑️  Akan dihapus {$count} data dengan user_id tidak valid dari tabel {$table}");
                    } else {
                        DB::table($table)
                            ->whereNotIn('user_id', User::pluck('id'))
                            ->delete();
                        $this->line("     ✅ Dihapus {$count} data dengan user_id tidak valid dari tabel {$table}");
                    }
                }
            }
        }
    }

    private function cleanDuplicateData($userId, $dryRun = false)
    {
        $this->info("   🔄 Membersihkan data duplikat...");

        // Hapus data duplikat berdasarkan kriteria tertentu
        $this->cleanDuplicateIncomes($userId, $dryRun);
        $this->cleanDuplicateExpenditures($userId, $dryRun);
        $this->cleanDuplicateProducts($userId, $dryRun);
    }

    private function cleanDuplicateIncomes($userId, $dryRun = false)
    {
        // Hapus income duplikat berdasarkan tanggal, produk, dan jumlah yang sama
        $duplicates = DB::table('incomes')
            ->where('user_id', $userId)
            ->select('tanggal', 'produk', 'jumlah_terjual', 'harga_satuan')
            ->groupBy('tanggal', 'produk', 'jumlah_terjual', 'harga_satuan')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            $count = DB::table('incomes')
                ->where('user_id', $userId)
                ->where('tanggal', $duplicate->tanggal)
                ->where('produk', $duplicate->produk)
                ->where('jumlah_terjual', $duplicate->jumlah_terjual)
                ->where('harga_satuan', $duplicate->harga_satuan)
                ->count();

            if ($count > 1) {
                $toDelete = $count - 1; // Sisakan 1 data
                if ($dryRun) {
                    $this->line("     🗑️  Akan dihapus {$toDelete} income duplikat untuk {$duplicate->produk} pada {$duplicate->tanggal}");
                } else {
                    DB::table('incomes')
                        ->where('user_id', $userId)
                        ->where('tanggal', $duplicate->tanggal)
                        ->where('produk', $duplicate->produk)
                        ->where('jumlah_terjual', $duplicate->jumlah_terjual)
                        ->where('harga_satuan', $duplicate->harga_satuan)
                        ->orderBy('id')
                        ->limit($toDelete)
                        ->delete();
                    $this->line("     ✅ Dihapus {$toDelete} income duplikat untuk {$duplicate->produk} pada {$duplicate->tanggal}");
                }
            }
        }
    }

    private function cleanDuplicateExpenditures($userId, $dryRun = false)
    {
        // Hapus expenditure duplikat berdasarkan tanggal, keterangan, dan jumlah yang sama
        $duplicates = DB::table('expenditures')
            ->where('user_id', $userId)
            ->select('tanggal', 'keterangan', 'jumlah')
            ->groupBy('tanggal', 'keterangan', 'jumlah')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            $count = DB::table('expenditures')
                ->where('user_id', $userId)
                ->where('tanggal', $duplicate->tanggal)
                ->where('keterangan', $duplicate->keterangan)
                ->where('jumlah', $duplicate->jumlah)
                ->count();

            if ($count > 1) {
                $toDelete = $count - 1; // Sisakan 1 data
                if ($dryRun) {
                    $this->line("     🗑️  Akan dihapus {$toDelete} expenditure duplikat untuk {$duplicate->keterangan} pada {$duplicate->tanggal}");
                } else {
                    DB::table('expenditures')
                        ->where('user_id', $userId)
                        ->where('tanggal', $duplicate->tanggal)
                        ->where('keterangan', $duplicate->keterangan)
                        ->where('jumlah', $duplicate->jumlah)
                        ->orderBy('id')
                        ->limit($toDelete)
                        ->delete();
                    $this->line("     ✅ Dihapus {$toDelete} expenditure duplikat untuk {$duplicate->keterangan} pada {$duplicate->tanggal}");
                }
            }
        }
    }

    private function cleanDuplicateProducts($userId, $dryRun = false)
    {
        // Hapus product duplikat berdasarkan nama yang sama
        $duplicates = DB::table('products')
            ->where('user_id', $userId)
            ->select('name')
            ->groupBy('name')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            $count = DB::table('products')
                ->where('user_id', $userId)
                ->where('name', $duplicate->name)
                ->count();

            if ($count > 1) {
                $toDelete = $count - 1; // Sisakan 1 data
                if ($dryRun) {
                    $this->line("     🗑️  Akan dihapus {$toDelete} product duplikat untuk {$duplicate->name}");
                } else {
                    DB::table('products')
                        ->where('user_id', $userId)
                        ->where('name', $duplicate->name)
                        ->orderBy('id')
                        ->limit($toDelete)
                        ->delete();
                    $this->line("     ✅ Dihapus {$toDelete} product duplikat untuk {$duplicate->name}");
                }
            }
        }
    }

    private function validateRelations($userId, $dryRun = false)
    {
        $this->info("   🔗 Validasi relasi antar tabel...");

        // Validasi product_id di incomes
        $invalidIncomes = DB::table('incomes')
            ->where('incomes.user_id', $userId)
            ->leftJoin('products', function($join) {
                $join->on('incomes.product_id', '=', 'products.id')
                     ->on('incomes.user_id', '=', 'products.user_id');
            })
            ->whereNull('products.id')
            ->count();

        if ($invalidIncomes > 0) {
            if ($dryRun) {
                $this->line("     ⚠️  Ditemukan {$invalidIncomes} income dengan product_id tidak valid");
            } else {
                DB::table('incomes')
                    ->where('user_id', $userId)
                    ->whereNotIn('product_id', function($query) use ($userId) {
                        $query->select('id')
                              ->from('products')
                              ->where('user_id', $userId);
                    })
                    ->update(['product_id' => null]);
                $this->line("     ✅ Diperbaiki {$invalidIncomes} income dengan product_id tidak valid");
            }
        }

        // Validasi product_id di stock_histories
        $invalidStockHistories = DB::table('stock_histories')
            ->where('stock_histories.user_id', $userId)
            ->leftJoin('products', function($join) {
                $join->on('stock_histories.product_id', '=', 'products.id')
                     ->on('stock_histories.user_id', '=', 'products.user_id');
            })
            ->whereNull('products.id')
            ->count();

        if ($invalidStockHistories > 0) {
            if ($dryRun) {
                $this->line("     ⚠️  Ditemukan {$invalidStockHistories} stock_history dengan product_id tidak valid");
            } else {
                DB::table('stock_histories')
                    ->where('user_id', $userId)
                    ->whereNotIn('product_id', function($query) use ($userId) {
                        $query->select('id')
                              ->from('products')
                              ->where('user_id', $userId);
                    })
                    ->delete();
                $this->line("     ✅ Dihapus {$invalidStockHistories} stock_history dengan product_id tidak valid");
            }
        }
    }
}
