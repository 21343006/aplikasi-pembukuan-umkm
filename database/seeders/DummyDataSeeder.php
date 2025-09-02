<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Income;
use App\Models\Expenditure;
use App\Models\Debt;
use App\Models\Receivable;
use App\Models\FixedCost; // Added FixedCost model
use Carbon\Carbon;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data
        $this->clearExistingData();

        // Create users
        $this->createUsers();

        // Create modal awal
        $this->createModalAwal();

        // Create products
        $this->createProducts();

        // Create incomes
        $this->createIncomes();

        // Create expenditures
        $this->createExpenditures();

        // Create fixed costs
        $this->createFixedCosts();

        // Create debts
        $this->createDebts();

        // Create receivables
        $this->createReceivables();

        // Sync modal awal ke tabel Capital
        $this->syncModalAwalToCapital();

        $this->command->info('Dummy data berhasil dibuat!');
    }

    private function clearExistingData(): void
    {
        $this->command->info('Menghapus data lama...');
        
        // Clear data in specific order to avoid foreign key constraints
        \App\Models\StockHistory::query()->delete();
        \App\Models\Income::query()->delete();
        \App\Models\Expenditure::query()->delete();
        \App\Models\FixedCost::query()->delete();
        \App\Models\Debt::query()->delete();
        \App\Models\Receivable::query()->delete();
        \App\Models\Capital::query()->delete();
        \App\Models\Capitalearly::query()->delete();
        \App\Models\Product::query()->delete();
        \App\Models\User::query()->delete();
        
        $this->command->info('Data lama berhasil dihapus.');
    }

    private function createUsers(): void
    {
        $this->command->info('Membuat user...');
        
        \App\Models\User::create([
            'name' => 'Admin UMKM',
            'email' => 'admin@umkm.com',
            'password' => bcrypt('password'),
            'business_name' => 'Warung Makan Sejahtera',
            'business_type' => 'Makanan & Minuman',
            'address' => 'Jl. Raya Utama No. 123, Jakarta',
            'phone' => '081234567890',
            'nib' => '1234567890123456',
            'initial_balance' => 100000000,
            'is_active' => true
        ]);
        
        $this->command->info('User berhasil dibuat.');
    }

    private function createModalAwal(): void
    {
        $this->command->info('Membuat modal awal...');
        
        // Ambil user yang sudah dibuat
        $user = \App\Models\User::first();
        if (!$user) {
            $this->command->error('User tidak ditemukan!');
            return;
        }
        
        \App\Models\Capitalearly::create([
            'user_id' => $user->id,
            'modal_awal' => 100000000, // 100 juta
            'tanggal_input' => '2025-01-01'
        ]);
        
        $this->command->info('Modal awal berhasil dibuat.');
    }

    private function syncModalAwalToCapital(): void
    {
        $this->command->info('Sinkronisasi modal awal ke tabel Capital...');
        
        // Ambil user yang sudah dibuat
        $user = \App\Models\User::first();
        if (!$user) {
            $this->command->error('User tidak ditemukan!');
            return;
        }
        
        // Ambil data modal awal
        $modalAwal = \App\Models\Capitalearly::where('user_id', $user->id)->first();
        
        if ($modalAwal) {
            // Cek apakah sudah ada data modal awal di tabel Capital
            $existingCapital = \App\Models\Capital::where('user_id', $user->id)
                ->where('jenis', 'masuk')
                ->where('keperluan', 'Modal Awal')
                ->first();
            
            if (!$existingCapital) {
                // Buat data modal awal di tabel Capital
                \App\Models\Capital::create([
                    'user_id' => $user->id,
                    'tanggal' => $modalAwal->tanggal_input,
                    'keperluan' => 'Modal Awal',
                    'keterangan' => 'Modal awal untuk memulai usaha',
                    'nominal' => $modalAwal->modal_awal,
                    'jenis' => 'masuk'
                ]);
                
                $this->command->info('Modal awal berhasil disinkronkan ke tabel Capital.');
            } else {
                $this->command->info('Data modal awal sudah ada di tabel Capital.');
            }
        }
    }

    private function createProducts(): void
    {
        $this->command->info('Creating products...\n');
        
        // Ambil user yang sudah dibuat
        $user = \App\Models\User::first();
        if (!$user) {
            $this->command->error('User tidak ditemukan!');
            return;
        }
        
        $userId = $user->id;
        $products = [];
        
        $productList = [
            'Bakso' => [
                'quantity' => 20000,
                'low_stock_threshold' => 50
            ],
            'Mie Ayam' => [
                'quantity' => 20000,
                'low_stock_threshold' => 30
            ]
        ];

        foreach ($productList as $name => $data) {
            $product = \App\Models\Product::create([
                'user_id' => $userId,
                'name' => $name,
                'quantity' => $data['quantity'],
                'low_stock_threshold' => $data['low_stock_threshold']
            ]);
            $products[$name] = $product;
            $this->command->info("Product {$name} created/updated with ID: {$product->id}\n");
        }
    }

    private function createIncomes(): void
    {
        $this->command->info("Creating income data...\n");
        
        // Ambil user yang sudah dibuat
        $user = \App\Models\User::first();
        if (!$user) {
            $this->command->error('User tidak ditemukan!');
            return;
        }
        
        $userId = $user->id;
        $produkList = ['Bakso', 'Mie Ayam'];

        $startDate = Carbon::create(2025, 1, 1);
        $endDate = Carbon::create(2028, 12, 31);

        while ($startDate->lte($endDate)) {
            // Buat 3-5 transaksi pendapatan per hari
            $jumlahTransaksi = rand(3, 5);
            for ($i = 0; $i < $jumlahTransaksi; $i++) {
                $produkKey = array_rand($produkList);
                $produkName = $produkList[$produkKey];
                
                // Ambil produk dari database
                $product = \App\Models\Product::where('name', $produkName)->first();
                if (!$product) continue;
                
                $jumlahTerjual = rand(10, 50); // Tingkatkan jumlah terjual
                $hargaSatuan = $produkName === 'Bakso' ? 15000 : 12000; // Harga tetap
                $totalPendapatan = $jumlahTerjual * $hargaSatuan;
                $biayaPerUnit = rand(8000, 12000); // Biaya per unit
                $laba = $totalPendapatan - ($jumlahTerjual * $biayaPerUnit);

                \App\Models\Income::create([
                    'user_id' => $userId,
                    'product_id' => $product->id,
                    'tanggal' => $startDate->toDateString(),
                    'jumlah_terjual' => $jumlahTerjual,
                    'harga_satuan' => $hargaSatuan,
                    'total_pendapatan' => $totalPendapatan,
                    'biaya_per_unit' => $biayaPerUnit,
                    'laba' => $laba
                ]);
            }

            $startDate->addDay();
        }
    }

    private function createExpenditures(): void
    {
        $this->command->info("Creating expenditures data...\n");
        
        // Ambil user yang sudah dibuat
        $user = \App\Models\User::first();
        if (!$user) {
            $this->command->error('User tidak ditemukan!');
            return;
        }
        
        $userId = $user->id;
        $pengeluaranList = [
            'Beli Daging Sapi' => [80000, 200000],     // Tingkatkan range
            'Beli Tepung & Bumbu' => [40000, 100000],  // Tingkatkan range
            'Beli Gas Elpiji' => [50000, 130000],      // Tingkatkan range
            'Beli Sayuran' => [30000, 80000],          // Tingkatkan range
            'Beli Minyak Goreng' => [40000, 90000],    // Tingkatkan range
            'Beli Mie Mentah' => [25000, 60000]        // Tingkatkan range
        ];

        $startDate = Carbon::create(2025, 1, 1);
        $endDate = Carbon::create(2028, 12, 31);

        while ($startDate->lte($endDate)) {
            // Tingkatkan frekuensi pengeluaran (2-4 kali per hari)
            $jumlahExpenditure = rand(2, 4);
            for ($i = 0; $i < $jumlahExpenditure; $i++) {
                $pengeluaranKey = array_rand($pengeluaranList);
                $pengeluaranRange = $pengeluaranList[$pengeluaranKey];
                
                \App\Models\Expenditure::create([
                    'user_id' => $userId,
                    'tanggal' => $startDate->toDateString(),
                    'keterangan' => $pengeluaranKey,
                    'jumlah' => rand($pengeluaranRange[0], $pengeluaranRange[1]),
                ]);
            }

            $startDate->addDay();
        }
    }

    private function createFixedCosts(): void
    {
        $this->command->info("Creating fixed costs data...\n");
        
        // Ambil user yang sudah dibuat
        $user = \App\Models\User::first();
        if (!$user) {
            $this->command->error('User tidak ditemukan!');
            return;
        }
        
        $userId = $user->id;
        $fixedCosts = [
            'Sewa Tempat' => 1000000,      // Rp 1.000.000 per bulan
            'Gaji Karyawan' => 2500000,    // Rp 2.500.000 per bulan
            'Listrik & Air' => 500000,     // Rp 500.000 per bulan
            'Internet' => 200000,          // Rp 200.000 per bulan
            'Pajak' => 300000              // Rp 300.000 per bulan
        ];

        $startDate = Carbon::create(2025, 1, 1);
        $endDate = Carbon::create(2028, 12, 31);

        while ($startDate->lte($endDate)) {
            foreach ($fixedCosts as $keperluan => $nominal) {
                \App\Models\FixedCost::create([
                    'user_id' => $userId,
                    'tanggal' => $startDate->toDateString(),
                    'keperluan' => $keperluan,
                    'nominal' => $nominal
                ]);
            }

            $startDate->addMonth();
        }
    }

    private function createDebts(): void
    {
        $this->command->info("Creating debts data...\n");
        
        // Ambil user yang sudah dibuat
        $user = \App\Models\User::first();
        if (!$user) {
            $this->command->error('User tidak ditemukan!');
            return;
        }
        
        $userId = $user->id;
        $creditors = [
            'PT Sukses Makmur' => 'Supplier daging sapi',
            'CV Jaya Abadi' => 'Supplier bumbu dan rempah',
            'UD Maju Bersama' => 'Supplier gas elpiji',
            'Toko Sumber Rejeki' => 'Supplier sayuran segar',
            'PT Indofood' => 'Supplier mie dan bumbu instan'
        ];

        $startDate = Carbon::create(2025, 1, 1);
        $endDate = Carbon::create(2028, 12, 31);

        while ($startDate->lte($endDate)) {
            // Buat 1-3 utang per bulan
            $jumlahUtang = rand(1, 3);
            for ($i = 0; $i < $jumlahUtang; $i++) {
                $creditorKey = array_rand($creditors);
                $creditorName = $creditorKey;
                $description = $creditors[$creditorKey];
                
                $amount = rand(500000, 2000000);
                $dueDate = $startDate->copy()->addDays(rand(7, 30));
                
                // Logika status yang lebih realistis
                $paymentScenario = rand(1, 4); // 1: lunas, 2: sebagian, 3: belum bayar, 4: terlambat
                
                switch ($paymentScenario) {
                    case 1: // Lunas
                        $paidAmount = $amount;
                        $status = 'paid';
                        $notes = 'Sudah dibayar tepat waktu';
                        $paidDate = $dueDate->copy()->subDays(rand(1, 10));
                        break;
                    case 2: // Dibayar sebagian
                        $paidAmount = rand($amount * 0.3, $amount * 0.7);
                        $status = 'partial';
                        $notes = 'Sudah dibayar sebagian';
                        $paidDate = $dueDate->copy()->subDays(rand(1, 5));
                        break;
                    case 3: // Belum dibayar (masih dalam waktu)
                        $paidAmount = 0;
                        $status = 'unpaid';
                        $notes = 'Belum dibayar';
                        $paidDate = null;
                        break;
                    case 4: // Terlambat (belum dibayar atau sebagian)
                        $paidAmount = rand(0, 1) ? rand($amount * 0.1, $amount * 0.5) : 0;
                        $status = $paidAmount > 0 ? 'partial' : 'unpaid';
                        $notes = $paidAmount > 0 ? 'Dibayar sebagian tapi terlambat' : 'Belum dibayar dan terlambat';
                        $paidDate = $paidAmount > 0 ? $dueDate->copy()->addDays(rand(1, 15)) : null;
                        // Set due date ke masa lalu untuk simulasi keterlambatan
                        $dueDate = $startDate->copy()->subDays(rand(1, 30));
                        break;
                }

                \App\Models\Debt::create([
                    'user_id' => $userId,
                    'creditor_name' => $creditorName,
                    'description' => $description,
                    'amount' => $amount,
                    'due_date' => $dueDate->toDateString(),
                    'status' => $status,
                    'paid_amount' => $paidAmount,
                    'paid_date' => $paidDate ? $paidDate->toDateString() : null,
                    'notes' => $notes,
                ]);
            }

            $startDate->addMonth();
        }
    }

    private function createReceivables(): void
    {
        $this->command->info("Creating receivables data...\n");
        
        // Ambil user yang sudah dibuat
        $user = \App\Models\User::first();
        if (!$user) {
            $this->command->error('User tidak ditemukan!');
            return;
        }
        
        $userId = $user->id;
        $debtors = [
            'Toko Makmur' => 'Penjualan bakso porsi besar',
            'Warung Pak Haji' => 'Penjualan mie ayam porsi besar',
            'Catering Sejahtera' => 'Pesanan untuk acara',
            'Kantin Sekolah' => 'Penjualan harian',
            'Rumah Makan Sederhana' => 'Penjualan grosir'
        ];

        $startDate = Carbon::create(2025, 1, 1);
        $endDate = Carbon::create(2028, 12, 31);

        while ($startDate->lte($endDate)) {
            // Buat 1-3 piutang per bulan
            $jumlahPiutang = rand(1, 3);
            for ($i = 0; $i < $jumlahPiutang; $i++) {
                $debtorKey = array_rand($debtors);
                $debtorName = $debtorKey;
                $description = $debtors[$debtorKey];
                
                $amount = rand(300000, 1500000);
                $dueDate = $startDate->copy()->addDays(rand(7, 30));
                
                // Logika status yang lebih realistis
                $paymentScenario = rand(1, 4); // 1: lunas, 2: sebagian, 3: belum bayar, 4: terlambat
                
                switch ($paymentScenario) {
                    case 1: // Lunas
                        $paidAmount = $amount;
                        $status = 'paid';
                        $notes = 'Sudah dibayar tepat waktu';
                        $paidDate = $dueDate->copy()->subDays(rand(1, 10));
                        break;
                    case 2: // Dibayar sebagian
                        $paidAmount = rand($amount * 0.3, $amount * 0.7);
                        $status = 'partial';
                        $notes = 'Sudah dibayar sebagian';
                        $paidDate = $dueDate->copy()->subDays(rand(1, 5));
                        break;
                    case 3: // Belum dibayar (masih dalam waktu)
                        $paidAmount = 0;
                        $status = 'unpaid';
                        $notes = 'Belum dibayar';
                        $paidDate = null;
                        break;
                    case 4: // Terlambat (belum dibayar atau sebagian)
                        $paidAmount = rand(0, 1) ? rand($amount * 0.1, $amount * 0.5) : 0;
                        $status = $paidAmount > 0 ? 'partial' : 'unpaid';
                        $notes = $paidAmount > 0 ? 'Dibayar sebagian tapi terlambat' : 'Belum dibayar dan terlambat';
                        $paidDate = $paidAmount > 0 ? $dueDate->copy()->addDays(rand(1, 15)) : null;
                        // Set due date ke masa lalu untuk simulasi keterlambatan
                        $dueDate = $startDate->copy()->subDays(rand(1, 30));
                        break;
                }

                \App\Models\Receivable::create([
                    'user_id' => $userId,
                    'debtor_name' => $debtorName,
                    'description' => $description,
                    'amount' => $amount,
                    'due_date' => $dueDate->toDateString(),
                    'status' => $status,
                    'paid_amount' => $paidAmount,
                    'paid_date' => $paidDate ? $paidDate->toDateString() : null,
                    'notes' => $notes,
                ]);
            }

            $startDate->addMonth();
        }
    }
}
