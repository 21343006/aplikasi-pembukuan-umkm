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

class RealisticBaksoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🎯 Memulai seeding data realistis untuk usaha Bakso...');

        $user = User::where('email', 'pakhaji@bakso.com')->first();
        
        if (!$user) {
            $this->command->error('❌ User Pak Haji tidak ditemukan. Jalankan BaksoMieAyamSeeder terlebih dahulu.');
            return;
        }

        // 1. Buat data penjualan dengan pola musiman yang realistis
        $this->createSeasonalSales($user);
        $this->command->info('✅ Data penjualan musiman berhasil dibuat');

        // 2. Buat data pengeluaran dengan pola yang realistis
        $this->createSeasonalExpenditures($user);
        $this->command->info('✅ Data pengeluaran musiman berhasil dibuat');

        // 3. Buat data event khusus (liburan, hari raya, dll)
        $this->createSpecialEventData($user);
        $this->command->info('✅ Data event khusus berhasil dibuat');

        // 4. Buat data stok dengan fluktuasi yang realistis
        $this->createRealisticStockData($user);
        $this->command->info('✅ Data stok realistis berhasil dibuat');

        $this->command->info('🎉 Data realistis berhasil dibuat!');
    }

    private function createSeasonalSales($user)
    {
        // Pola penjualan yang realistis berdasarkan musim dan hari
        $seasonalPatterns = [
            // September 2025 - Awal musim
            ['month' => 9, 'year' => 2025, 'multiplier' => 0.8, 'weekend_boost' => 1.3],
            // Oktober 2025 - Musim normal
            ['month' => 10, 'year' => 2025, 'multiplier' => 1.0, 'weekend_boost' => 1.4],
            // November 2025 - Musim hujan, penjualan naik
            ['month' => 11, 'year' => 2025, 'multiplier' => 1.2, 'weekend_boost' => 1.5],
            // Desember 2025 - Liburan, penjualan tinggi
            ['month' => 12, 'year' => 2025, 'multiplier' => 1.5, 'weekend_boost' => 1.8],
            // Januari 2026 - Awal tahun, penjualan normal
            ['month' => 1, 'year' => 2026, 'multiplier' => 1.0, 'weekend_boost' => 1.3],
            // Februari 2026 - Musim normal
            ['month' => 2, 'year' => 2026, 'multiplier' => 0.9, 'weekend_boost' => 1.4],
            // Maret 2026 - Musim semi, penjualan naik
            ['month' => 3, 'year' => 2026, 'multiplier' => 1.1, 'weekend_boost' => 1.5],
            // April 2026 - Musim normal
            ['month' => 4, 'year' => 2026, 'multiplier' => 1.0, 'weekend_boost' => 1.4],
            // Mei 2026 - Musim panas, penjualan tinggi
            ['month' => 5, 'year' => 2026, 'multiplier' => 1.3, 'weekend_boost' => 1.6],
            // Juni 2026 - Puncak musim panas
            ['month' => 6, 'year' => 2026, 'multiplier' => 1.4, 'weekend_boost' => 1.7],
            // Juli 2026 - Musim panas, penjualan tinggi
            ['month' => 7, 'year' => 2026, 'multiplier' => 1.3, 'weekend_boost' => 1.6],
            // Agustus 2026 - Akhir musim panas
            ['month' => 8, 'year' => 2026, 'multiplier' => 1.2, 'weekend_boost' => 1.5],
        ];

        foreach ($seasonalPatterns as $pattern) {
            $this->createMonthlySeasonalSales($user, $pattern['month'], $pattern['year'], $pattern['multiplier'], $pattern['weekend_boost']);
        }
    }

    private function createMonthlySeasonalSales($user, $month, $year, $multiplier, $weekendBoost)
    {
        $products = [
            'Bakso Sapi' => ['base_quantity' => 120, 'price' => 25000, 'cost' => 15000],
            'Mie Ayam' => ['base_quantity' => 100, 'price' => 20000, 'cost' => 12000],
            'Bakso Ikan' => ['base_quantity' => 80, 'price' => 18000, 'cost' => 10000],
            'Es Teh Manis' => ['base_quantity' => 180, 'price' => 5000, 'cost' => 2000],
            'Es Jeruk' => ['base_quantity' => 120, 'price' => 7000, 'cost' => 3000],
        ];

        $daysInMonth = Carbon::create($year, $month)->daysInMonth;
        
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($year, $month, $day);
            $isWeekend = $date->isWeekend();
            $dayMultiplier = $isWeekend ? $weekendBoost : 1.0;
            
            // Tambah variasi harian (±20%)
            $dailyVariation = rand(80, 120) / 100;
            $finalMultiplier = $multiplier * $dayMultiplier * $dailyVariation;

            foreach ($products as $productName => $productData) {
                $baseQuantity = $productData['base_quantity'];
                $finalQuantity = max(1, round($baseQuantity * $finalMultiplier / 30)); // Bagi per hari
                
                if ($finalQuantity > 0) {
                    $this->createSeasonalIncome($user, $date, $productName, $finalQuantity, $productData['price'], $productData['cost']);
                }
            }
        }
    }

    private function createSeasonalIncome($user, $date, $productName, $quantity, $price, $cost)
    {
        $product = Product::where('user_id', $user->id)->where('name', $productName)->first();
        
        $income = Income::create([
            'user_id' => $user->id,
            'tanggal' => $date,
            'produk' => $productName,
            'jumlah_terjual' => $quantity,
            'harga_satuan' => $price,
            'biaya_per_unit' => $cost,
            'total_pendapatan' => $quantity * $price,
            'laba' => ($price - $cost) * $quantity,
        ]);

        // Update stok
        if ($product) {
            $this->updateStock($product, $quantity, 'out', $income);
        }
    }

    private function createSeasonalExpenditures($user)
    {
        // Pola pengeluaran yang realistis berdasarkan musim
        $seasonalExpenditures = [
            // September 2025 - Persiapan awal
            ['month' => 9, 'year' => 2025, 'multiplier' => 1.2, 'items' => [
                'Pembelian Peralatan Dapur' => 2000000,
                'Pembelian Bahan Baku Awal' => 5000000,
                'Pembelian Kemasan' => 1000000,
            ]],
            // Oktober 2025 - Operasional normal
            ['month' => 10, 'year' => 2025, 'multiplier' => 1.0, 'items' => [
                'Restock Bahan Baku' => 4000000,
                'Pembelian Kemasan' => 800000,
            ]],
            // November 2025 - Musim hujan, biaya naik
            ['month' => 11, 'year' => 2025, 'multiplier' => 1.1, 'items' => [
                'Restock Bahan Baku' => 4500000,
                'Pembelian Kemasan' => 900000,
                'Biaya Transport Tambahan' => 300000,
            ]],
            // Desember 2025 - Liburan, persiapan besar
            ['month' => 12, 'year' => 2025, 'multiplier' => 1.4, 'items' => [
                'Persiapan Stok Liburan' => 8000000,
                'Pembelian Kemasan Tambahan' => 1500000,
                'Pembelian Bumbu Tambahan' => 1200000,
                'Biaya Marketing Liburan' => 1000000,
            ]],
            // Januari 2026 - Awal tahun, normal
            ['month' => 1, 'year' => 2026, 'multiplier' => 1.0, 'items' => [
                'Restock Pasca Liburan' => 5000000,
                'Pembelian Kemasan' => 800000,
            ]],
            // Juni 2026 - Puncak musim panas
            ['month' => 6, 'year' => 2026, 'multiplier' => 1.3, 'items' => [
                'Persiapan Stok Puncak' => 7000000,
                'Pembelian Kemasan Puncak' => 1200000,
                'Biaya Marketing Musim Panas' => 800000,
            ]],
        ];

        foreach ($seasonalExpenditures as $season) {
            foreach ($season['items'] as $keterangan => $baseAmount) {
                $finalAmount = round($baseAmount * $season['multiplier'] * (rand(90, 110) / 100), -3);
                
                Expenditure::create([
                    'user_id' => $user->id,
                    'tanggal' => Carbon::create($season['year'], $season['month'], rand(1, 28)),
                    'keterangan' => $keterangan,
                    'jumlah' => $finalAmount,
                ]);
            }
        }
    }

    private function createSpecialEventData($user)
    {
        // Data untuk event khusus
        $specialEvents = [
            // Hari Raya Idul Fitri 2026 (estimasi)
            ['date' => '2026-04-10', 'event' => 'Hari Raya Idul Fitri', 'multiplier' => 2.0],
            ['date' => '2026-04-11', 'event' => 'Hari Raya Idul Fitri', 'multiplier' => 2.0],
            ['date' => '2026-04-12', 'event' => 'Hari Raya Idul Fitri', 'multiplier' => 1.8],
            
            // Natal & Tahun Baru 2025
            ['date' => '2025-12-24', 'event' => 'Malam Natal', 'multiplier' => 1.8],
            ['date' => '2025-12-25', 'event' => 'Hari Natal', 'multiplier' => 2.0],
            ['date' => '2025-12-31', 'event' => 'Malam Tahun Baru', 'multiplier' => 1.9],
            ['date' => '2026-01-01', 'event' => 'Tahun Baru', 'multiplier' => 2.0],
            
            // Libur Nasional
            ['date' => '2025-10-28', 'event' => 'Hari Sumpah Pemuda', 'multiplier' => 1.3],
            ['date' => '2025-11-10', 'event' => 'Hari Pahlawan', 'multiplier' => 1.3],
            ['date' => '2026-02-14', 'event' => 'Valentine Day', 'multiplier' => 1.4],
            ['date' => '2026-05-01', 'event' => 'Hari Buruh', 'multiplier' => 1.2],
            ['date' => '2026-08-17', 'event' => 'Hari Kemerdekaan', 'multiplier' => 1.5],
        ];

        foreach ($specialEvents as $event) {
            $this->createSpecialEventSales($user, $event['date'], $event['event'], $event['multiplier']);
        }
    }

    private function createSpecialEventSales($user, $date, $event, $multiplier)
    {
        $products = [
            'Bakso Sapi' => ['base_quantity' => 150, 'price' => 25000, 'cost' => 15000],
            'Mie Ayam' => ['base_quantity' => 120, 'price' => 20000, 'cost' => 12000],
            'Bakso Ikan' => ['base_quantity' => 100, 'price' => 18000, 'cost' => 10000],
            'Es Teh Manis' => ['base_quantity' => 200, 'price' => 5000, 'cost' => 2000],
            'Es Jeruk' => ['base_quantity' => 150, 'price' => 7000, 'cost' => 3000],
        ];

        foreach ($products as $productName => $productData) {
            $baseQuantity = $productData['base_quantity'];
            $finalQuantity = round($baseQuantity * $multiplier);
            
            if ($finalQuantity > 0) {
                $this->createSeasonalIncome($user, Carbon::parse($date), $productName, $finalQuantity, $productData['price'], $productData['cost']);
            }
        }
    }

    private function createRealisticStockData($user)
    {
        $products = Product::where('user_id', $user->id)->get();
        
        foreach ($products as $product) {
            // Buat data stok masuk yang realistis
            $stockInPatterns = [
                // Restock mingguan
                ['frequency' => 'weekly', 'quantity' => rand(15, 30)],
                // Restock bulanan
                ['frequency' => 'monthly', 'quantity' => rand(40, 80)],
                // Restock khusus event
                ['frequency' => 'event', 'quantity' => rand(60, 100)],
            ];

            foreach ($stockInPatterns as $pattern) {
                $this->createStockInByPattern($product, $pattern);
            }
        }
    }

    private function createStockInByPattern($product, $pattern)
    {
        $dates = [];
        
        switch ($pattern['frequency']) {
            case 'weekly':
                // Restock setiap minggu
                for ($year = 2025; $year <= 2026; $year++) {
                    for ($month = 9; $month <= 12; $month++) {
                        if ($year === 2025 && $month < 9) continue;
                        for ($week = 1; $week <= 4; $week++) {
                            $dates[] = Carbon::create($year, $month, $week * 7);
                        }
                    }
                }
                break;
                
            case 'monthly':
                // Restock setiap bulan
                for ($year = 2025; $year <= 2026; $year++) {
                    for ($month = 9; $month <= 12; $month++) {
                        if ($year === 2025 && $month < 9) continue;
                        $dates[] = Carbon::create($year, $month, 15);
                    }
                }
                break;
                
            case 'event':
                // Restock untuk event khusus
                $eventDates = [
                    '2025-12-20', '2026-01-05', '2026-04-05', '2026-06-10', '2026-08-10'
                ];
                foreach ($eventDates as $date) {
                    $dates[] = Carbon::parse($date);
                }
                break;
        }

        foreach ($dates as $date) {
            $quantity = $pattern['quantity'] + rand(-5, 5); // Tambah variasi
            if ($quantity > 0) {
                $this->updateStock($product, $quantity, 'in', null, 'Restock ' . $product->name . ' - ' . $pattern['frequency']);
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
