<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Capitalearly;
use App\Models\FixedCost;
use App\Models\Income;
use App\Models\Expenditure;
use App\Models\Product;
use App\Models\StockHistory;
use App\Models\Capital;
use App\Models\Debt;
use App\Models\Receivable;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class BaksoMieAyamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Memulai seeding data untuk usaha Bakso & Mie Ayam...');

        // 1. Buat User
        $user = $this->createUser();
        $this->command->info('✅ User berhasil dibuat');

        // 2. Buat Modal Awal
        $this->createInitialCapital($user);
        $this->command->info('✅ Modal awal berhasil dibuat');

        // 3. Buat Biaya Tetap
        $this->createFixedCosts($user);
        $this->command->info('✅ Biaya tetap berhasil dibuat');

        // 4. Buat Produk dan Stok
        $this->createProductsAndStock($user);
        $this->command->info('✅ Produk dan stok berhasil dibuat');

        // 5. Buat Data Penjualan (Income)
        $this->createIncomes($user);
        $this->command->info('✅ Data penjualan berhasil dibuat');

        // 6. Buat Data Pengeluaran
        $this->createExpenditures($user);
        $this->command->info('✅ Data pengeluaran berhasil dibuat');

        // 7. Buat Data Modal
        $this->createCapitals($user);
        $this->command->info('✅ Data modal berhasil dibuat');

        // 8. Buat Data Utang & Piutang
        $this->createDebtsAndReceivables($user);
        $this->command->info('✅ Data utang & piutang berhasil dibuat');

        $this->command->info('🎉 Semua data berhasil dibuat!');
        $this->command->info('📊 Usaha: Bakso & Mie Ayam Pak Haji');
        $this->command->info('👤 User: pakhaji@bakso.com');
        $this->command->info('🔑 Password: password');
    }

    private function createUser()
    {
        return User::create([
            'name' => 'Pak Haji Ahmad',
            'email' => 'pakhaji@bakso.com',
            'password' => Hash::make('password'),
            'business_name' => 'Bakso & Mie Ayam Pak Haji',
            'phone' => '081234567890',
            'address' => 'Jl. Raya Bakso No. 123, Jakarta Selatan',
            'business_type' => 'Makanan & Minuman',
            'initial_balance' => 50000000, // 50 juta modal awal
            'is_active' => true,
            'nib' => '1234567890123456',
        ]);
    }

    private function createInitialCapital($user)
    {
        Capitalearly::create([
            'user_id' => $user->id,
            'modal_awal' => 50000000,
        ]);
    }

    private function createFixedCosts($user)
    {
        $fixedCosts = [
            // Sewa Tempat - Lebih realistis untuk UMKM
            ['keperluan' => 'Sewa Tempat', 'nominal' => 2500000, 'frequency' => 'monthly'],
            // Listrik - Sesuaikan dengan penggunaan komersial
            ['keperluan' => 'Listrik', 'nominal' => 800000, 'frequency' => 'monthly'],
            // Air - Untuk usaha makanan
            ['keperluan' => 'Air', 'nominal' => 300000, 'frequency' => 'monthly'],
            // Internet - Untuk marketing dan operasional
            ['keperluan' => 'Internet', 'nominal' => 200000, 'frequency' => 'monthly'],
            // Gaji Karyawan - Lebih realistis untuk UMKM
            ['keperluan' => 'Gaji Karyawan', 'nominal' => 4000000, 'frequency' => 'monthly'],
            // BPJS Karyawan - Sesuaikan dengan gaji
            ['keperluan' => 'BPJS Karyawan', 'nominal' => 400000, 'frequency' => 'monthly'],
            // Pajak UMKM - Lebih realistis
            ['keperluan' => 'Pajak UMKM', 'nominal' => 300000, 'frequency' => 'monthly'],
            // Izin Usaha - Yearly
            ['keperluan' => 'Izin Usaha', 'nominal' => 500000, 'frequency' => 'yearly'],
            // Maintenance Peralatan - Lebih realistis
            ['keperluan' => 'Maintenance Peralatan', 'nominal' => 300000, 'frequency' => 'monthly'],
            // Biaya Kebersihan
            ['keperluan' => 'Kebersihan & Sanitasi', 'nominal' => 200000, 'frequency' => 'monthly'],
            // Biaya Marketing Digital
            ['keperluan' => 'Marketing Digital', 'nominal' => 150000, 'frequency' => 'monthly'],
        ];

        foreach ($fixedCosts as $cost) {
            if ($cost['frequency'] === 'monthly') {
                // Buat untuk setiap bulan 2025-2026
                for ($year = 2025; $year <= 2026; $year++) {
                    for ($month = 1; $month <= 12; $month++) {
                        FixedCost::create([
                            'user_id' => $user->id,
                            'tanggal' => Carbon::create($year, $month, 1),
                            'keperluan' => $cost['keperluan'],
                            'nominal' => $cost['nominal'],
                        ]);
                    }
                }
            } else {
                // Yearly costs
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

    private function createProductsAndStock($user)
    {
        $products = [
            [
                'name' => 'Bakso Sapi',
                'quantity' => 200,
                'low_stock_threshold' => 40,
                'unit' => 'porsi',
                'cost_per_unit' => 18000, // Biaya naik sedikit
                'selling_price' => 30000, // Harga jual naik signifikan
            ],
            [
                'name' => 'Mie Ayam',
                'quantity' => 180,
                'low_stock_threshold' => 35,
                'unit' => 'porsi',
                'cost_per_unit' => 15000, // Biaya naik sedikit
                'selling_price' => 25000, // Harga jual naik signifikan
            ],
            [
                'name' => 'Bakso Ikan',
                'quantity' => 120,
                'low_stock_threshold' => 25,
                'unit' => 'porsi',
                'cost_per_unit' => 13000, // Biaya naik sedikit
                'selling_price' => 22000, // Harga jual naik signifikan
            ],
            [
                'name' => 'Es Teh Manis',
                'quantity' => 300,
                'low_stock_threshold' => 50,
                'unit' => 'gelas',
                'cost_per_unit' => 3000, // Biaya naik sedikit
                'selling_price' => 8000, // Harga jual naik signifikan
            ],
            [
                'name' => 'Es Jeruk',
                'quantity' => 200,
                'low_stock_threshold' => 40,
                'unit' => 'gelas',
                'cost_per_unit' => 4000, // Biaya naik sedikit
                'selling_price' => 10000, // Harga jual naik signifikan
            ],
            [
                'name' => 'Bakso Urat', // Produk baru premium
                'quantity' => 80,
                'low_stock_threshold' => 15,
                'unit' => 'porsi',
                'cost_per_unit' => 22000, // Biaya tinggi karena premium
                'selling_price' => 35000, // Harga premium
            ],
            [
                'name' => 'Mie Goreng', // Produk baru populer
                'quantity' => 150,
                'low_stock_threshold' => 30,
                'unit' => 'porsi',
                'cost_per_unit' => 18000, // Biaya tinggi karena mie goreng
                'selling_price' => 28000, // Harga kompetitif
            ],
        ];

        foreach ($products as $productData) {
            $product = Product::create([
                'user_id' => $user->id,
                'name' => $productData['name'],
                'quantity' => $productData['quantity'],
                'low_stock_threshold' => $productData['low_stock_threshold'],
            ]);

            // Buat stock history untuk stok awal
            StockHistory::create([
                'product_id' => $product->id,
                'user_id' => $user->id,
                'type' => 'initial',
                'quantity_change' => $productData['quantity'],
                'quantity_before' => 0,
                'quantity_after' => $productData['quantity'],
                'description' => 'Stok awal ' . $productData['name'],
                'reference_type' => 'initial',
            ]);

            // Simpan data produk untuk digunakan di income
            $product->cost_per_unit = $productData['cost_per_unit'];
            $product->selling_price = $productData['selling_price'];
            $product->unit = $productData['unit'];
            
            // Update produk dengan data tambahan
            $product->update([
                'cost_per_unit' => $productData['cost_per_unit'],
                'selling_price' => $productData['selling_price'],
                'unit' => $productData['unit'],
            ]);
        }
    }

    private function createIncomes($user)
    {
        $products = Product::where('user_id', $user->id)->get();
        
        // Data penjualan yang lebih menguntungkan untuk usaha bakso & mie ayam
        $salesData = [
            // Januari 2025 - Mulai dengan volume yang lebih tinggi
            [
                'month' => 1, 'year' => 2025,
                'bakso_sapi' => [200, 30000], // [jumlah, harga] - Naik dari 80 ke 200, harga dari 25k ke 30k
                'mie_ayam' => [180, 25000],   // Naik dari 70 ke 180, harga dari 20k ke 25k
                'bakso_ikan' => [120, 22000], // Naik dari 50 ke 120, harga dari 18k ke 22k
                'es_teh' => [300, 8000],      // Naik dari 120 ke 300, harga dari 5k ke 8k
                'es_jeruk' => [200, 10000],   // Naik dari 80 ke 200, harga dari 7k ke 10k
                'bakso_urat' => [80, 35000],  // Produk baru yang premium
                'mie_goreng' => [150, 28000], // Produk baru yang populer
            ],
            // Februari 2025
            [
                'month' => 2, 'year' => 2025,
                'bakso_sapi' => [220, 30000],
                'mie_ayam' => [200, 25000],
                'bakso_ikan' => [140, 22000],
                'es_teh' => [350, 8000],
                'es_jeruk' => [220, 10000],
                'bakso_urat' => [90, 35000],
                'mie_goreng' => [170, 28000],
            ],
            // Maret 2025
            [
                'month' => 3, 'year' => 2025,
                'bakso_sapi' => [250, 30000],
                'mie_ayam' => [230, 25000],
                'bakso_ikan' => [160, 22000],
                'es_teh' => [400, 8000],
                'es_jeruk' => [250, 10000],
                'bakso_urat' => [100, 35000],
                'mie_goreng' => [200, 28000],
            ],
            // April 2025
            [
                'month' => 4, 'year' => 2025,
                'bakso_sapi' => [280, 30000],
                'mie_ayam' => [260, 25000],
                'bakso_ikan' => [180, 22000],
                'es_teh' => [450, 8000],
                'es_jeruk' => [280, 10000],
                'bakso_urat' => [110, 35000],
                'mie_goreng' => [230, 28000],
            ],
            // Mei 2025
            [
                'month' => 5, 'year' => 2025,
                'bakso_sapi' => [300, 30000],
                'mie_ayam' => [280, 25000],
                'bakso_ikan' => [200, 22000],
                'es_teh' => [500, 8000],
                'es_jeruk' => [300, 10000],
                'bakso_urat' => [120, 35000],
                'mie_goreng' => [250, 28000],
            ],
            // Juni 2025
            [
                'month' => 6, 'year' => 2025,
                'bakso_sapi' => [320, 30000],
                'mie_ayam' => [300, 25000],
                'bakso_ikan' => [220, 22000],
                'es_teh' => [550, 8000],
                'es_jeruk' => [320, 10000],
                'bakso_urat' => [130, 35000],
                'mie_goreng' => [270, 28000],
            ],
            // Juli 2025
            [
                'month' => 7, 'year' => 2025,
                'bakso_sapi' => [350, 30000],
                'mie_ayam' => [330, 25000],
                'bakso_ikan' => [240, 22000],
                'es_teh' => [600, 8000],
                'es_jeruk' => [350, 10000],
                'bakso_urat' => [140, 35000],
                'mie_goreng' => [300, 28000],
            ],
            // Agustus 2025
            [
                'month' => 8, 'year' => 2025,
                'bakso_sapi' => [380, 30000],
                'mie_ayam' => [360, 25000],
                'bakso_ikan' => [260, 22000],
                'es_teh' => [650, 8000],
                'es_jeruk' => [380, 10000],
                'bakso_urat' => [150, 35000],
                'mie_goreng' => [330, 28000],
            ],
            // September 2025
            [
                'month' => 9, 'year' => 2025,
                'bakso_sapi' => [400, 30000],
                'mie_ayam' => [380, 25000],
                'bakso_ikan' => [280, 22000],
                'es_teh' => [700, 8000],
                'es_jeruk' => [400, 10000],
                'bakso_urat' => [160, 35000],
                'mie_goreng' => [350, 28000],
            ],
            // Oktober 2025
            [
                'month' => 10, 'year' => 2025,
                'bakso_sapi' => [420, 30000],
                'mie_ayam' => [400, 25000],
                'bakso_ikan' => [300, 22000],
                'es_teh' => [750, 8000],
                'es_jeruk' => [420, 10000],
                'bakso_urat' => [170, 35000],
                'mie_goreng' => [370, 28000],
            ],
            // November 2025
            [
                'month' => 11, 'year' => 2025,
                'bakso_sapi' => [450, 30000],
                'mie_ayam' => [420, 25000],
                'bakso_ikan' => [320, 22000],
                'es_teh' => [800, 8000],
                'es_jeruk' => [450, 10000],
                'bakso_urat' => [180, 35000],
                'mie_goreng' => [400, 28000],
            ],
            // Desember 2025
            [
                'month' => 12, 'year' => 2025,
                'bakso_sapi' => [500, 30000],
                'mie_ayam' => [480, 25000],
                'bakso_ikan' => [350, 22000],
                'es_teh' => [850, 8000],
                'es_jeruk' => [500, 10000],
                'bakso_urat' => [200, 35000],
                'mie_goreng' => [450, 28000],
            ],
            // Januari 2026 - Tahun baru dengan pertumbuhan lebih agresif
            [
                'month' => 1, 'year' => 2026,
                'bakso_sapi' => [480, 32000], // Harga naik lagi untuk tahun baru
                'mie_ayam' => [460, 27000],
                'bakso_ikan' => [330, 24000],
                'es_teh' => [800, 9000],
                'es_jeruk' => [480, 11000],
                'bakso_urat' => [190, 37000],
                'mie_goreng' => [430, 30000],
            ],
            // Februari 2026
            [
                'month' => 2, 'year' => 2026,
                'bakso_sapi' => [500, 32000],
                'mie_ayam' => [480, 27000],
                'bakso_ikan' => [350, 24000],
                'es_teh' => [850, 9000],
                'es_jeruk' => [500, 11000],
                'bakso_urat' => [200, 37000],
                'mie_goreng' => [450, 30000],
            ],
            // Maret 2026
            [
                'month' => 3, 'year' => 2026,
                'bakso_sapi' => [520, 32000],
                'mie_ayam' => [500, 27000],
                'bakso_ikan' => [370, 24000],
                'es_teh' => [900, 9000],
                'es_jeruk' => [520, 11000],
                'bakso_urat' => [210, 37000],
                'mie_goreng' => [470, 30000],
            ],
            // April 2026
            [
                'month' => 4, 'year' => 2026,
                'bakso_sapi' => [550, 32000],
                'mie_ayam' => [530, 27000],
                'bakso_ikan' => [400, 24000],
                'es_teh' => [950, 9000],
                'es_jeruk' => [550, 11000],
                'bakso_urat' => [220, 37000],
                'mie_goreng' => [500, 30000],
            ],
            // Mei 2026
            [
                'month' => 5, 'year' => 2026,
                'bakso_sapi' => [580, 32000],
                'mie_ayam' => [560, 27000],
                'bakso_ikan' => [430, 24000],
                'es_teh' => [1000, 9000],
                'es_jeruk' => [580, 11000],
                'bakso_urat' => [230, 37000],
                'mie_goreng' => [530, 30000],
            ],
            // Juni 2026
            [
                'month' => 6, 'year' => 2026,
                'bakso_sapi' => [600, 32000],
                'mie_ayam' => [580, 27000],
                'bakso_ikan' => [450, 24000],
                'es_teh' => [1050, 9000],
                'es_jeruk' => [600, 11000],
                'bakso_urat' => [240, 37000],
                'mie_goreng' => [550, 30000],
            ],
            // Juli 2026
            [
                'month' => 7, 'year' => 2026,
                'bakso_sapi' => [620, 32000],
                'mie_ayam' => [600, 27000],
                'bakso_ikan' => [470, 24000],
                'es_teh' => [1100, 9000],
                'es_jeruk' => [620, 11000],
                'bakso_urat' => [250, 37000],
                'mie_goreng' => [570, 30000],
            ],
            // Agustus 2026
            [
                'month' => 8, 'year' => 2026,
                'bakso_sapi' => [650, 32000],
                'mie_ayam' => [630, 27000],
                'bakso_ikan' => [500, 24000],
                'es_teh' => [1150, 9000],
                'es_jeruk' => [650, 11000],
                'bakso_urat' => [270, 37000],
                'mie_goreng' => [600, 30000],
            ],
            // September 2026
            [
                'month' => 9, 'year' => 2026,
                'bakso_sapi' => [680, 32000],
                'mie_ayam' => [660, 27000],
                'bakso_ikan' => [530, 24000],
                'es_teh' => [1200, 9000],
                'es_jeruk' => [680, 11000],
                'bakso_urat' => [290, 37000],
                'mie_goreng' => [630, 30000],
            ],
            // Oktober 2026
            [
                'month' => 10, 'year' => 2026,
                'bakso_sapi' => [700, 32000],
                'mie_ayam' => [680, 27000],
                'bakso_ikan' => [550, 24000],
                'es_teh' => [1250, 9000],
                'es_jeruk' => [700, 11000],
                'bakso_urat' => [310, 37000],
                'mie_goreng' => [650, 30000],
            ],
            // November 2026
            [
                'month' => 11, 'year' => 2026,
                'bakso_sapi' => [720, 32000],
                'mie_ayam' => [700, 27000],
                'bakso_ikan' => [570, 24000],
                'es_teh' => [1300, 9000],
                'es_jeruk' => [720, 11000],
                'bakso_urat' => [330, 37000],
                'mie_goreng' => [680, 30000],
            ],
            // Desember 2026
            [
                'month' => 12, 'year' => 2026,
                'bakso_sapi' => [750, 32000],
                'mie_ayam' => [730, 27000],
                'bakso_ikan' => [600, 24000],
                'es_teh' => [1350, 9000],
                'es_jeruk' => [750, 11000],
                'bakso_urat' => [350, 37000],
                'mie_goreng' => [700, 30000],
            ],
        ];

        foreach ($salesData as $monthData) {
            $month = $monthData['month'];
            $year = $monthData['year'];
            
            // Buat data penjualan untuk setiap produk
            $this->createMonthlyIncome($user, $month, $year, 'Bakso Sapi', $monthData['bakso_sapi'][0], $monthData['bakso_sapi'][1]);
            $this->createMonthlyIncome($user, $month, $year, 'Mie Ayam', $monthData['mie_ayam'][0], $monthData['mie_ayam'][1]);
            $this->createMonthlyIncome($user, $month, $year, 'Bakso Ikan', $monthData['bakso_ikan'][0], $monthData['bakso_ikan'][1]);
            $this->createMonthlyIncome($user, $month, $year, 'Es Teh Manis', $monthData['es_teh'][0], $monthData['es_teh'][1]);
            $this->createMonthlyIncome($user, $month, $year, 'Es Jeruk', $monthData['es_jeruk'][0], $monthData['es_jeruk'][1]);
            $this->createMonthlyIncome($user, $month, $year, 'Bakso Urat', $monthData['bakso_urat'][0], $monthData['bakso_urat'][1]);
            $this->createMonthlyIncome($user, $month, $year, 'Mie Goreng', $monthData['mie_goreng'][0], $monthData['mie_goreng'][1]);
        }
    }

    private function createMonthlyIncome($user, $month, $year, $productName, $quantity, $price)
    {
        // Buat beberapa transaksi dalam satu bulan untuk realistis
        $transactionsPerMonth = rand(15, 25); // 15-25 transaksi per bulan
        $quantityPerTransaction = ceil($quantity / $transactionsPerMonth);
        
        for ($i = 0; $i < $transactionsPerMonth; $i++) {
            $day = rand(1, Carbon::create($year, $month)->daysInMonth);
            $actualQuantity = $i === $transactionsPerMonth - 1 ? $quantity - ($quantityPerTransaction * ($transactionsPerMonth - 1)) : $quantityPerTransaction;
            
            if ($actualQuantity <= 0) continue;
            
            $product = Product::where('user_id', $user->id)->where('name', $productName)->first();
            $costPerUnit = $product ? $product->cost_per_unit : 0;
            
            $income = Income::create([
                'user_id' => $user->id,
                'tanggal' => Carbon::create($year, $month, $day),
                'produk' => $productName,
                'jumlah_terjual' => $actualQuantity,
                'harga_satuan' => $price,
                'biaya_per_unit' => $costPerUnit,
                'total_pendapatan' => $actualQuantity * $price,
                'laba' => ($price - $costPerUnit) * $actualQuantity,
            ]);

            // Update stok
            if ($product) {
                $this->updateStock($product, $actualQuantity, 'out', $income);
            }
        }
    }

    private function updateStock($product, $quantity, $type, $reference = null)
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
            'description' => $type === 'out' ? 'Penjualan ' . $product->name : 'Stok masuk ' . $product->name,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference ? $reference->id : null,
        ]);
    }

    private function createExpenditures($user)
    {
        $expenditures = [
            // Bahan Baku
            ['keterangan' => 'Daging Sapi', 'amount' => 8000000, 'frequency' => 'monthly'],
            ['keterangan' => 'Daging Ayam', 'amount' => 5000000, 'frequency' => 'monthly'],
            ['keterangan' => 'Ikan Tenggiri', 'amount' => 3000000, 'frequency' => 'monthly'],
            ['keterangan' => 'Mie Basah', 'amount' => 2000000, 'frequency' => 'monthly'],
            ['keterangan' => 'Tepung Terigu', 'amount' => 1500000, 'frequency' => 'monthly'],
            ['keterangan' => 'Telur', 'amount' => 1000000, 'frequency' => 'monthly'],
            ['keterangan' => 'Sayuran', 'amount' => 800000, 'frequency' => 'monthly'],
            ['keterangan' => 'Bumbu Masak', 'amount' => 1200000, 'frequency' => 'monthly'],
            ['keterangan' => 'Minyak Goreng', 'amount' => 1000000, 'frequency' => 'monthly'],
            ['keterangan' => 'Gas LPG', 'amount' => 600000, 'frequency' => 'monthly'],
            
            // Kemasan
            ['keterangan' => 'Piring & Mangkok', 'amount' => 500000, 'frequency' => 'monthly'],
            ['keterangan' => 'Sendok & Garpu', 'amount' => 300000, 'frequency' => 'monthly'],
            ['keterangan' => 'Tissue', 'amount' => 200000, 'frequency' => 'monthly'],
            ['keterangan' => 'Kantong Plastik', 'amount' => 150000, 'frequency' => 'monthly'],
            
            // Operasional
            ['keterangan' => 'Transport Bahan', 'amount' => 400000, 'frequency' => 'monthly'],
            ['keterangan' => 'Kebersihan', 'amount' => 300000, 'frequency' => 'monthly'],
            ['keterangan' => 'Marketing', 'amount' => 500000, 'frequency' => 'monthly'],
        ];

        foreach ($expenditures as $expenditure) {
            if ($expenditure['frequency'] === 'monthly') {
                // Buat untuk setiap bulan 2025-2026
                for ($year = 2025; $year <= 2026; $year++) {
                    for ($month = 1; $month <= 12; $month++) {
                        // Tambah variasi untuk realistis
                        $variation = rand(-10, 10) / 100; // ±10% variasi
                        $amount = $expenditure['amount'] * (1 + $variation);
                        
                        Expenditure::create([
                            'user_id' => $user->id,
                            'tanggal' => Carbon::create($year, $month, rand(1, 28)),
                            'keterangan' => $expenditure['keterangan'],
                            'jumlah' => round($amount, -3), // Bulatkan ke ribuan
                        ]);
                    }
                }
            }
        }
    }

    private function createCapitals($user)
    {
        $capitals = [
            // Modal Masuk - HAPUS "Modal Awal" karena sudah ada di Capitalearly
            ['keperluan' => 'Investasi Peralatan', 'keterangan' => 'Pembelian kompor, wajan, dll', 'nominal' => 15000000, 'jenis' => 'masuk'],
            ['keperluan' => 'Renovasi Tempat', 'keterangan' => 'Renovasi dapur dan area makan', 'nominal' => 10000000, 'jenis' => 'masuk'],
            
            // Modal Keluar
            ['keperluan' => 'Pembayaran Utang', 'keterangan' => 'Pelunasan utang supplier', 'nominal' => 8000000, 'jenis' => 'keluar'],
            ['keperluan' => 'Pembayaran Sewa', 'keterangan' => 'Pembayaran sewa tempat 6 bulan', 'nominal' => 48000000, 'jenis' => 'keluar'],
        ];

        // Buat modal dengan tanggal yang spesifik dan realistis
        $capitalData = [
            [
                'keperluan' => 'Investasi Peralatan',
                'keterangan' => 'Pembelian kompor, wajan, dll',
                'nominal' => 15000000,
                'jenis' => 'masuk',
                'tanggal' => Carbon::create(2025, 1, 12), // Tanggal spesifik
            ],
            [
                'keperluan' => 'Renovasi Tempat',
                'keterangan' => 'Renovasi dapur dan area makan',
                'nominal' => 10000000,
                'jenis' => 'masuk',
                'tanggal' => Carbon::create(2025, 1, 20), // Tanggal spesifik
            ],
            [
                'keperluan' => 'Pembayaran Utang',
                'keterangan' => 'Pelunasan utang supplier',
                'nominal' => 8000000,
                'jenis' => 'keluar',
                'tanggal' => Carbon::create(2025, 1, 15), // Tanggal spesifik
            ],
            [
                'keperluan' => 'Pembayaran Sewa',
                'keterangan' => 'Pembayaran sewa tempat 6 bulan',
                'nominal' => 48000000,
                'jenis' => 'keluar',
                'tanggal' => Carbon::create(2025, 1, 25), // Tanggal spesifik
            ],
        ];

        foreach ($capitalData as $capital) {
            Capital::create([
                'user_id' => $user->id,
                'tanggal' => $capital['tanggal'],
                'keperluan' => $capital['keperluan'],
                'keterangan' => $capital['keterangan'],
                'nominal' => $capital['nominal'],
                'jenis' => $capital['jenis'],
            ]);
        }
    }

    private function createDebtsAndReceivables($user)
    {
        // Utang ke Supplier
        $debts = [
            [
                'creditor_name' => 'PT Sukses Makmur Jaya',
                'description' => 'Utang bahan baku daging sapi dan ayam',
                'amount' => 15000000,
                'due_date' => Carbon::create(2026, 3, 15),
                'status' => 'unpaid',
                'notes' => 'Supplier bahan baku utama',
            ],
            [
                'creditor_name' => 'CV Bumbu Nusantara',
                'description' => 'Utang bumbu masak dan rempah',
                'amount' => 5000000,
                'due_date' => Carbon::create(2026, 2, 28),
                'status' => 'partial',
                'paid_amount' => 2000000,
                'notes' => 'Supplier bumbu dan rempah',
            ],
        ];

        foreach ($debts as $debt) {
            Debt::create([
                'user_id' => $user->id,
                'creditor_name' => $debt['creditor_name'],
                'description' => $debt['description'],
                'amount' => $debt['amount'],
                'due_date' => $debt['due_date'],
                'status' => $debt['status'],
                'paid_amount' => $debt['paid_amount'] ?? 0,
                'notes' => $debt['notes'],
            ]);
        }

        // Piutang dari Pelanggan
        $receivables = [
            [
                'debtor_name' => 'Kantor PT Maju Bersama',
                'description' => 'Pesanan catering untuk acara kantor',
                'amount' => 8000000,
                'due_date' => Carbon::create(2026, 1, 31),
                'status' => 'unpaid',
                'notes' => 'Catering 200 porsi untuk acara kantor',
            ],
            [
                'debtor_name' => 'Sekolah SDN Harapan Bangsa',
                'description' => 'Pesanan makan siang siswa',
                'amount' => 3000000,
                'due_date' => Carbon::create(2026, 2, 15),
                'status' => 'partial',
                'paid_amount' => 1000000,
                'notes' => 'Makan siang 150 siswa selama 1 minggu',
            ],
        ];

        foreach ($receivables as $receivable) {
            Receivable::create([
                'user_id' => $user->id,
                'debtor_name' => $receivable['debtor_name'],
                'description' => $receivable['description'],
                'amount' => $receivable['amount'],
                'due_date' => $receivable['due_date'],
                'status' => $receivable['status'],
                'paid_amount' => $receivable['paid_amount'] ?? 0,
                'notes' => $receivable['notes'],
            ]);
        }
    }
}
