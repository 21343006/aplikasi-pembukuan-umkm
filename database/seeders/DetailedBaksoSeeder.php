<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Income;
use App\Models\Expenditure;
use App\Models\FixedCost;
use App\Models\Product;
use App\Models\StockHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DetailedBaksoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🍜 Memulai seeding data detail untuk usaha Bakso...');

        $user = User::where('email', 'pakhaji@bakso.com')->first();
        
        if (!$user) {
            $this->command->error('❌ User Pak Haji tidak ditemukan. Jalankan BaksoMieAyamSeeder terlebih dahulu.');
            return;
        }

        // 1. Tambah data penjualan harian yang lebih detail
        $this->createDetailedDailySales($user);
        $this->command->info('✅ Data penjualan harian detail berhasil dibuat');

        // 2. Tambah data pengeluaran harian yang lebih detail
        $this->createDetailedDailyExpenditures($user);
        $this->command->info('✅ Data pengeluaran harian detail berhasil dibuat');

        // 3. Tambah data stok masuk untuk restock
        $this->createStockInData($user);
        $this->command->info('✅ Data stok masuk berhasil dibuat');

        // 4. Tambah data biaya tetap tambahan
        $this->createAdditionalFixedCosts($user);
        $this->command->info('✅ Data biaya tetap tambahan berhasil dibuat');

        $this->command->info('🎉 Data detail berhasil dibuat!');
    }

    private function createDetailedDailySales($user)
    {
        // Data penjualan harian yang lebih realistis
        $dailySales = [
            // September 2025 - Minggu Pertama
            ['date' => '2025-09-01', 'bakso_sapi' => 8, 'mie_ayam' => 6, 'bakso_ikan' => 4, 'es_teh' => 12, 'es_jeruk' => 8],
            ['date' => '2025-09-02', 'bakso_sapi' => 10, 'mie_ayam' => 8, 'bakso_ikan' => 5, 'es_teh' => 15, 'es_jeruk' => 10],
            ['date' => '2025-09-03', 'bakso_sapi' => 12, 'mie_ayam' => 10, 'bakso_ikan' => 6, 'es_teh' => 18, 'es_jeruk' => 12],
            ['date' => '2025-09-04', 'bakso_sapi' => 15, 'mie_ayam' => 12, 'bakso_ikan' => 8, 'es_teh' => 22, 'es_jeruk' => 15],
            ['date' => '2025-09-05', 'bakso_sapi' => 18, 'mie_ayam' => 15, 'bakso_ikan' => 10, 'es_teh' => 25, 'es_jeruk' => 18],
            ['date' => '2025-09-06', 'bakso_sapi' => 20, 'mie_ayam' => 18, 'bakso_ikan' => 12, 'es_teh' => 28, 'es_jeruk' => 20],
            ['date' => '2025-09-07', 'bakso_sapi' => 22, 'mie_ayam' => 20, 'bakso_ikan' => 14, 'es_teh' => 30, 'es_jeruk' => 22],
            
            // Oktober 2025 - Minggu Kedua
            ['date' => '2025-10-15', 'bakso_sapi' => 25, 'mie_ayam' => 22, 'bakso_ikan' => 16, 'es_teh' => 35, 'es_jeruk' => 25],
            ['date' => '2025-10-16', 'bakso_sapi' => 28, 'mie_ayam' => 25, 'bakso_ikan' => 18, 'es_teh' => 38, 'es_jeruk' => 28],
            ['date' => '2025-10-17', 'bakso_sapi' => 30, 'mie_ayam' => 28, 'bakso_ikan' => 20, 'es_teh' => 40, 'es_jeruk' => 30],
            
            // Desember 2025 - Liburan
            ['date' => '2025-12-24', 'bakso_sapi' => 35, 'mie_ayam' => 32, 'bakso_ikan' => 25, 'es_teh' => 45, 'es_jeruk' => 35],
            ['date' => '2025-12-25', 'bakso_sapi' => 40, 'mie_ayam' => 38, 'bakso_ikan' => 30, 'es_teh' => 50, 'es_jeruk' => 40],
            ['date' => '2025-12-26', 'bakso_sapi' => 38, 'mie_ayam' => 35, 'bakso_ikan' => 28, 'es_teh' => 48, 'es_jeruk' => 38],
            
            // Januari 2026 - Tahun Baru
            ['date' => '2026-01-01', 'bakso_sapi' => 45, 'mie_ayam' => 42, 'bakso_ikan' => 35, 'es_teh' => 55, 'es_jeruk' => 45],
            ['date' => '2026-01-02', 'bakso_sapi' => 42, 'mie_ayam' => 40, 'bakso_ikan' => 32, 'es_teh' => 52, 'es_jeruk' => 42],
            
            // Juni 2026 - Puncak Musim
            ['date' => '2026-06-15', 'bakso_sapi' => 50, 'mie_ayam' => 48, 'bakso_ikan' => 40, 'es_teh' => 60, 'es_jeruk' => 50],
            ['date' => '2026-06-16', 'bakso_sapi' => 48, 'mie_ayam' => 45, 'bakso_ikan' => 38, 'es_teh' => 58, 'es_jeruk' => 48],
        ];

        foreach ($dailySales as $sale) {
            $date = Carbon::parse($sale['date']);
            
            // Buat income untuk setiap produk
            $this->createDailyIncome($user, $date, 'Bakso Sapi', $sale['bakso_sapi'], 25000);
            $this->createDailyIncome($user, $date, 'Mie Ayam', $sale['mie_ayam'], 20000);
            $this->createDailyIncome($user, $date, 'Bakso Ikan', $sale['bakso_ikan'], 18000);
            $this->createDailyIncome($user, $date, 'Es Teh Manis', $sale['es_teh'], 5000);
            $this->createDailyIncome($user, $date, 'Es Jeruk', $sale['es_jeruk'], 7000);
        }
    }

    private function createDailyIncome($user, $date, $productName, $quantity, $price)
    {
        if ($quantity <= 0) return;

        $product = Product::where('user_id', $user->id)->where('name', $productName)->first();
        $costPerUnit = $product ? $product->cost_per_unit : 0;

        $income = Income::create([
            'user_id' => $user->id,
            'tanggal' => $date,
            'produk' => $productName,
            'jumlah_terjual' => $quantity,
            'harga_satuan' => $price,
            'biaya_per_unit' => $costPerUnit,
            'total_pendapatan' => $quantity * $price,
            'laba' => ($price - $costPerUnit) * $quantity,
        ]);

        // Update stok
        if ($product) {
            $this->updateStock($product, $quantity, 'out', $income);
        }
    }

    private function createDetailedDailyExpenditures($user)
    {
        // Data pengeluaran harian yang lebih detail
        $dailyExpenditures = [
            ['date' => '2025-09-01', 'keterangan' => 'Pembelian Daging Sapi Segar', 'jumlah' => 2500000],
            ['date' => '2025-09-01', 'keterangan' => 'Pembelian Daging Ayam', 'jumlah' => 1500000],
            ['date' => '2025-09-02', 'keterangan' => 'Pembelian Ikan Tenggiri', 'jumlah' => 800000],
            ['date' => '2025-09-02', 'keterangan' => 'Pembelian Mie Basah', 'jumlah' => 500000],
            ['date' => '2025-09-03', 'keterangan' => 'Pembelian Tepung Terigu', 'jumlah' => 300000],
            ['date' => '2025-09-03', 'keterangan' => 'Pembelian Telur', 'jumlah' => 200000],
            ['date' => '2025-09-04', 'keterangan' => 'Pembelian Sayuran', 'jumlah' => 250000],
            ['date' => '2025-09-04', 'keterangan' => 'Pembelian Bumbu Masak', 'jumlah' => 400000],
            ['date' => '2025-09-05', 'keterangan' => 'Pembelian Minyak Goreng', 'jumlah' => 300000],
            ['date' => '2025-09-05', 'keterangan' => 'Pembelian Gas LPG', 'jumlah' => 150000],
            
            // Oktober 2025
            ['date' => '2025-10-15', 'keterangan' => 'Restock Daging Sapi', 'jumlah' => 3000000],
            ['date' => '2025-10-15', 'keterangan' => 'Restock Daging Ayam', 'jumlah' => 1800000],
            ['date' => '2025-10-16', 'keterangan' => 'Pembelian Kemasan Baru', 'jumlah' => 800000],
            ['date' => '2025-10-17', 'keterangan' => 'Pembelian Peralatan Dapur', 'jumlah' => 1200000],
            
            // Desember 2025 - Persiapan Liburan
            ['date' => '2025-12-20', 'keterangan' => 'Persiapan Stok Liburan', 'jumlah' => 5000000],
            ['date' => '2025-12-21', 'keterangan' => 'Pembelian Kemasan Tambahan', 'jumlah' => 1000000],
            ['date' => '2025-12-22', 'keterangan' => 'Pembelian Bumbu Tambahan', 'jumlah' => 800000],
            
            // Januari 2026
            ['date' => '2026-01-05', 'keterangan' => 'Restock Pasca Liburan', 'jumlah' => 4000000],
            ['date' => '2026-01-10', 'keterangan' => 'Pembelian Peralatan Baru', 'jumlah' => 2000000],
            
            // Juni 2026 - Puncak Musim
            ['date' => '2026-06-10', 'keterangan' => 'Persiapan Stok Puncak', 'jumlah' => 6000000],
            ['date' => '2026-06-12', 'keterangan' => 'Pembelian Kemasan Puncak', 'jumlah' => 1500000],
        ];

        foreach ($dailyExpenditures as $expenditure) {
            Expenditure::create([
                'user_id' => $user->id,
                'tanggal' => Carbon::parse($expenditure['date']),
                'keterangan' => $expenditure['keterangan'],
                'jumlah' => $expenditure['jumlah'],
            ]);
        }
    }

    private function createStockInData($user)
    {
        $products = Product::where('user_id', $user->id)->get();
        
        foreach ($products as $product) {
            // Buat beberapa data stok masuk untuk restock
            $stockInDates = [
                '2025-09-15', '2025-10-15', '2025-11-15', '2025-12-15',
                '2026-01-15', '2026-02-15', '2026-03-15', '2026-04-15',
                '2026-05-15', '2026-06-15', '2026-07-15', '2026-08-15'
            ];

            foreach ($stockInDates as $date) {
                $quantity = rand(20, 50); // Stok masuk 20-50 unit
                
                $this->updateStock($product, $quantity, 'in', null, 'Restock ' . $product->name);
            }
        }
    }

    private function createAdditionalFixedCosts($user)
    {
        $additionalCosts = [
            ['keperluan' => 'Asuransi Usaha', 'nominal' => 2000000, 'frequency' => 'yearly'],
            ['keperluan' => 'Sertifikasi Halal', 'nominal' => 1500000, 'frequency' => 'yearly'],
            ['keperluan' => 'Pelatihan Karyawan', 'nominal' => 3000000, 'frequency' => 'yearly'],
            ['keperluan' => 'Konsultan Keuangan', 'nominal' => 1000000, 'frequency' => 'yearly'],
            ['keperluan' => 'Biaya Legal', 'nominal' => 800000, 'frequency' => 'yearly'],
        ];

        foreach ($additionalCosts as $cost) {
            if ($cost['frequency'] === 'yearly') {
                FixedCost::create([
                    'user_id' => $user->id,
                    'tanggal' => Carbon::create(2025, 9, 1),
                    'keperluan' => $cost['keperluan'],
                    'nominal' => $cost['nominal'],
                ]);
                
                FixedCost::create([
                    'user_id' => $user->id,
                    'tanggal' => Carbon::create(2026, 1, 1),
                    'keperluan' => $cost['keperluan'],
                    'nominal' => $cost['nominal'],
                ]);
            }
        }
    }

    private function updateStock($product, $quantity, $type, $reference = null, $description = null)
    {
        $quantityBefore = $product->quantity;
        $quantityChange = $type === 'out' ? -$quantity : $quantity;
        $quantityAfter = $quantityBefore + $quantityChange;

        // Update stok produk
        $product->update(['quantity' => $quantityAfter]);

        // Buat history stok
        StockHistory::create([
            'product_id' => $product->id,
            'user_id' => $product->user_id,
            'type' => $type,
            'quantity_change' => $quantityChange,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $quantityAfter,
            'description' => $description ?: ($type === 'out' ? 'Penjualan ' . $product->name : 'Stok masuk ' . $product->name),
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference ? $reference->id : null,
        ]);
    }
}
