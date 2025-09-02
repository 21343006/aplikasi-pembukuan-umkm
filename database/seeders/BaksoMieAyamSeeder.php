<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use App\Models\Income;
use App\Models\Expenditure;
use App\Models\Capital;
use App\Models\FixedCost;
use App\Models\Debt;
use App\Models\Receivable;
use App\Models\StockHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class BaksoMieAyamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat user untuk usaha bakso dan mie ayam
        $user = User::create([
            'name' => 'Pak Ahmad',
            'email' => 'ahmad@bakso.com',
            'password' => Hash::make('password123'),
            'business_name' => 'Warung Bakso Pak Ahmad',
            'business_type' => 'Makanan & Minuman',
            'address' => 'Jl. Raya Utama No. 45, Jakarta Selatan',
            'phone' => '0812-3456-7890',
            'nib' => '1234567890123456',
            'initial_balance' => 5000000,
            'is_active' => true,
        ]);

        // Buat produk-produk
        $products = [
            [
                'name' => 'Bakso Sapi',
                'quantity' => 100,
                'low_stock_threshold' => 20,
            ],
            [
                'name' => 'Bakso Ayam',
                'quantity' => 80,
                'low_stock_threshold' => 15,
            ],
            [
                'name' => 'Mie Ayam',
                'quantity' => 120,
                'low_stock_threshold' => 25,
            ],
            [
                'name' => 'Mie Ayam Bakso',
                'quantity' => 90,
                'low_stock_threshold' => 20,
            ],
            [
                'name' => 'Es Teh Manis',
                'quantity' => 200,
                'low_stock_threshold' => 30,
            ],
            [
                'name' => 'Es Jeruk',
                'quantity' => 150,
                'low_stock_threshold' => 25,
            ],
        ];

        foreach ($products as $productData) {
            Product::create(array_merge($productData, ['user_id' => $user->id]));
        }

        // Buat modal awal
        $user->capitalearly()->create([
            'tanggal_input' => Carbon::now()->subMonths(3),
            'modal_awal' => 5000000,
        ]);

        // Buat modal masuk dan keluar
        $capitals = [
            // Modal Masuk
            [
                'tanggal' => Carbon::now()->subMonths(2),
                'keperluan' => 'Pinjaman Bank',
                'keterangan' => 'Pinjaman untuk pembelian peralatan dapur',
                'nominal' => 2000000,
                'jenis' => 'masuk',
            ],
            [
                'tanggal' => Carbon::now()->subMonths(1),
                'keperluan' => 'Investasi Teman',
                'keterangan' => 'Investasi dari teman untuk ekspansi',
                'nominal' => 1000000,
                'jenis' => 'masuk',
            ],
            // Modal Keluar
            [
                'tanggal' => Carbon::now()->subMonths(2),
                'keperluan' => 'Pembelian Peralatan Dapur',
                'keterangan' => 'Kompor, panci, wajan, dan peralatan masak lainnya',
                'nominal' => 1500000,
                'jenis' => 'keluar',
            ],
            [
                'tanggal' => Carbon::now()->subMonths(1),
                'keperluan' => 'Renovasi Warung',
                'keterangan' => 'Cat ulang, perbaikan atap, dan dekorasi',
                'nominal' => 800000,
                'jenis' => 'keluar',
            ],
        ];

        foreach ($capitals as $capitalData) {
            $user->capitals()->create($capitalData);
        }

        // Buat biaya tetap
        $fixedCosts = [
            [
                'tanggal' => Carbon::now()->subMonths(3),
                'keperluan' => 'Sewa Tempat',
                'nominal' => 1500000,
            ],
            [
                'tanggal' => Carbon::now()->subMonths(2),
                'keperluan' => 'Sewa Tempat',
                'nominal' => 1500000,
            ],
            [
                'tanggal' => Carbon::now()->subMonths(1),
                'keperluan' => 'Sewa Tempat',
                'nominal' => 1500000,
            ],
            [
                'tanggal' => Carbon::now(),
                'keperluan' => 'Sewa Tempat',
                'nominal' => 1500000,
            ],
            [
                'tanggal' => Carbon::now()->subMonths(3),
                'keperluan' => 'Gaji Karyawan',
                'nominal' => 1200000,
            ],
            [
                'tanggal' => Carbon::now()->subMonths(2),
                'keperluan' => 'Gaji Karyawan',
                'nominal' => 1200000,
            ],
            [
                'tanggal' => Carbon::now()->subMonths(1),
                'keperluan' => 'Gaji Karyawan',
                'nominal' => 1200000,
            ],
            [
                'tanggal' => Carbon::now(),
                'keperluan' => 'Gaji Karyawan',
                'nominal' => 1200000,
            ],
            [
                'tanggal' => Carbon::now()->subMonths(3),
                'keperluan' => 'Listrik & Air',
                'nominal' => 300000,
            ],
            [
                'tanggal' => Carbon::now()->subMonths(2),
                'keperluan' => 'Listrik & Air',
                'nominal' => 350000,
            ],
            [
                'tanggal' => Carbon::now()->subMonths(1),
                'keperluan' => 'Listrik & Air',
                'nominal' => 320000,
            ],
            [
                'tanggal' => Carbon::now(),
                'keperluan' => 'Listrik & Air',
                'nominal' => 380000,
            ],
        ];

        foreach ($fixedCosts as $fixedCostData) {
            $user->fixedCosts()->create($fixedCostData);
        }

        // Buat pengeluaran
        $expenditures = [
            [
                'tanggal' => Carbon::now()->subDays(30),
                'keterangan' => 'Beli daging sapi untuk bakso',
                'jumlah' => 800000,
            ],
            [
                'tanggal' => Carbon::now()->subDays(28),
                'keterangan' => 'Beli daging ayam',
                'jumlah' => 500000,
            ],
            [
                'tanggal' => Carbon::now()->subDays(25),
                'keterangan' => 'Beli mie basah',
                'jumlah' => 200000,
            ],
            [
                'tanggal' => Carbon::now()->subDays(22),
                'keterangan' => 'Beli bumbu dan rempah',
                'jumlah' => 150000,
            ],
            [
                'tanggal' => Carbon::now()->subDays(20),
                'keterangan' => 'Beli sayuran (sawi, tauge)',
                'jumlah' => 100000,
            ],
            [
                'tanggal' => Carbon::now()->subDays(18),
                'keterangan' => 'Beli es batu dan minuman',
                'jumlah' => 80000,
            ],
            [
                'tanggal' => Carbon::now()->subDays(15),
                'keterangan' => 'Beli daging sapi',
                'jumlah' => 750000,
            ],
            [
                'tanggal' => Carbon::now()->subDays(12),
                'keterangan' => 'Beli daging ayam',
                'jumlah' => 450000,
            ],
            [
                'tanggal' => Carbon::now()->subDays(10),
                'keterangan' => 'Beli mie basah',
                'jumlah' => 180000,
            ],
            [
                'tanggal' => Carbon::now()->subDays(8),
                'keterangan' => 'Beli bumbu dan rempah',
                'jumlah' => 120000,
            ],
            [
                'tanggal' => Carbon::now()->subDays(5),
                'keterangan' => 'Beli sayuran',
                'jumlah' => 90000,
            ],
            [
                'tanggal' => Carbon::now()->subDays(3),
                'keterangan' => 'Beli es batu dan minuman',
                'jumlah' => 70000,
            ],
            [
                'tanggal' => Carbon::now()->subDays(1),
                'keterangan' => 'Beli daging sapi',
                'jumlah' => 800000,
            ],
        ];

        foreach ($expenditures as $expenditureData) {
            $user->expenditures()->create($expenditureData);
        }

        // Buat pemasukan
        $incomes = [
            // Bakso Sapi
            [
                'product_id' => 1,
                'tanggal' => Carbon::now()->subDays(30),
                'jumlah_terjual' => 25,
                'harga_satuan' => 15000,
                'biaya_per_unit' => 8000,
            ],
            [
                'product_id' => 1,
                'tanggal' => Carbon::now()->subDays(25),
                'jumlah_terjual' => 30,
                'harga_satuan' => 15000,
                'biaya_per_unit' => 8000,
            ],
            [
                'product_id' => 1,
                'tanggal' => Carbon::now()->subDays(20),
                'jumlah_terjual' => 28,
                'harga_satuan' => 15000,
                'biaya_per_unit' => 8000,
            ],
            [
                'product_id' => 1,
                'tanggal' => Carbon::now()->subDays(15),
                'jumlah_terjual' => 32,
                'harga_satuan' => 15000,
                'biaya_per_unit' => 8000,
            ],
            [
                'product_id' => 1,
                'tanggal' => Carbon::now()->subDays(10),
                'jumlah_terjual' => 35,
                'harga_satuan' => 15000,
                'biaya_per_unit' => 8000,
            ],
            [
                'product_id' => 1,
                'tanggal' => Carbon::now()->subDays(5),
                'jumlah_terjual' => 40,
                'harga_satuan' => 15000,
                'biaya_per_unit' => 8000,
            ],
            [
                'product_id' => 1,
                'tanggal' => Carbon::now()->subDays(1),
                'jumlah_terjual' => 38,
                'harga_satuan' => 15000,
                'biaya_per_unit' => 8000,
            ],
            // Bakso Ayam
            [
                'product_id' => 2,
                'tanggal' => Carbon::now()->subDays(30),
                'jumlah_terjual' => 20,
                'harga_satuan' => 12000,
                'biaya_per_unit' => 6000,
            ],
            [
                'product_id' => 2,
                'tanggal' => Carbon::now()->subDays(25),
                'jumlah_terjual' => 25,
                'harga_satuan' => 12000,
                'biaya_per_unit' => 6000,
            ],
            [
                'product_id' => 2,
                'tanggal' => Carbon::now()->subDays(20),
                'jumlah_terjual' => 22,
                'harga_satuan' => 12000,
                'biaya_per_unit' => 6000,
            ],
            [
                'product_id' => 2,
                'tanggal' => Carbon::now()->subDays(15),
                'jumlah_terjual' => 28,
                'harga_satuan' => 12000,
                'biaya_per_unit' => 6000,
            ],
            [
                'product_id' => 2,
                'tanggal' => Carbon::now()->subDays(10),
                'jumlah_terjual' => 30,
                'harga_satuan' => 12000,
                'biaya_per_unit' => 6000,
            ],
            [
                'product_id' => 2,
                'tanggal' => Carbon::now()->subDays(5),
                'jumlah_terjual' => 32,
                'harga_satuan' => 12000,
                'biaya_per_unit' => 6000,
            ],
            [
                'product_id' => 2,
                'tanggal' => Carbon::now()->subDays(1),
                'jumlah_terjual' => 35,
                'harga_satuan' => 12000,
                'biaya_per_unit' => 6000,
            ],
            // Mie Ayam
            [
                'product_id' => 3,
                'tanggal' => Carbon::now()->subDays(30),
                'jumlah_terjual' => 30,
                'harga_satuan' => 18000,
                'biaya_per_unit' => 9000,
            ],
            [
                'product_id' => 3,
                'tanggal' => Carbon::now()->subDays(25),
                'jumlah_terjual' => 35,
                'harga_satuan' => 18000,
                'biaya_per_unit' => 9000,
            ],
            [
                'product_id' => 3,
                'tanggal' => Carbon::now()->subDays(20),
                'jumlah_terjual' => 32,
                'harga_satuan' => 18000,
                'biaya_per_unit' => 9000,
            ],
            [
                'product_id' => 3,
                'tanggal' => Carbon::now()->subDays(15),
                'jumlah_terjual' => 38,
                'harga_satuan' => 18000,
                'biaya_per_unit' => 9000,
            ],
            [
                'product_id' => 3,
                'tanggal' => Carbon::now()->subDays(10),
                'jumlah_terjual' => 40,
                'harga_satuan' => 18000,
                'biaya_per_unit' => 9000,
            ],
            [
                'product_id' => 3,
                'tanggal' => Carbon::now()->subDays(5),
                'jumlah_terjual' => 42,
                'harga_satuan' => 18000,
                'biaya_per_unit' => 9000,
            ],
            [
                'product_id' => 3,
                'tanggal' => Carbon::now()->subDays(1),
                'jumlah_terjual' => 45,
                'harga_satuan' => 18000,
                'biaya_per_unit' => 9000,
            ],
            // Mie Ayam Bakso
            [
                'product_id' => 4,
                'tanggal' => Carbon::now()->subDays(30),
                'jumlah_terjual' => 25,
                'harga_satuan' => 22000,
                'biaya_per_unit' => 11000,
            ],
            [
                'product_id' => 4,
                'tanggal' => Carbon::now()->subDays(25),
                'jumlah_terjual' => 28,
                'harga_satuan' => 22000,
                'biaya_per_unit' => 11000,
            ],
            [
                'product_id' => 4,
                'tanggal' => Carbon::now()->subDays(20),
                'jumlah_terjual' => 30,
                'harga_satuan' => 22000,
                'biaya_per_unit' => 11000,
            ],
            [
                'product_id' => 4,
                'tanggal' => Carbon::now()->subDays(15),
                'jumlah_terjual' => 32,
                'harga_satuan' => 22000,
                'biaya_per_unit' => 11000,
            ],
            [
                'product_id' => 4,
                'tanggal' => Carbon::now()->subDays(10),
                'jumlah_terjual' => 35,
                'harga_satuan' => 22000,
                'biaya_per_unit' => 11000,
            ],
            [
                'product_id' => 4,
                'tanggal' => Carbon::now()->subDays(5),
                'jumlah_terjual' => 38,
                'harga_satuan' => 22000,
                'biaya_per_unit' => 11000,
            ],
            [
                'product_id' => 4,
                'tanggal' => Carbon::now()->subDays(1),
                'jumlah_terjual' => 40,
                'harga_satuan' => 22000,
                'biaya_per_unit' => 11000,
            ],
            // Es Teh Manis
            [
                'product_id' => 5,
                'tanggal' => Carbon::now()->subDays(30),
                'jumlah_terjual' => 50,
                'harga_satuan' => 3000,
                'biaya_per_unit' => 1000,
            ],
            [
                'product_id' => 5,
                'tanggal' => Carbon::now()->subDays(25),
                'jumlah_terjual' => 55,
                'harga_satuan' => 3000,
                'biaya_per_unit' => 1000,
            ],
            [
                'product_id' => 5,
                'tanggal' => Carbon::now()->subDays(20),
                'jumlah_terjual' => 60,
                'harga_satuan' => 3000,
                'biaya_per_unit' => 1000,
            ],
            [
                'product_id' => 5,
                'tanggal' => Carbon::now()->subDays(15),
                'jumlah_terjual' => 65,
                'harga_satuan' => 3000,
                'biaya_per_unit' => 1000,
            ],
            [
                'product_id' => 5,
                'tanggal' => Carbon::now()->subDays(10),
                'jumlah_terjual' => 70,
                'harga_satuan' => 3000,
                'biaya_per_unit' => 1000,
            ],
            [
                'product_id' => 5,
                'tanggal' => Carbon::now()->subDays(5),
                'jumlah_terjual' => 75,
                'harga_satuan' => 3000,
                'biaya_per_unit' => 1000,
            ],
            [
                'product_id' => 5,
                'tanggal' => Carbon::now()->subDays(1),
                'jumlah_terjual' => 80,
                'harga_satuan' => 3000,
                'biaya_per_unit' => 1000,
            ],
            // Es Jeruk
            [
                'product_id' => 6,
                'tanggal' => Carbon::now()->subDays(30),
                'jumlah_terjual' => 40,
                'harga_satuan' => 4000,
                'biaya_per_unit' => 1500,
            ],
            [
                'product_id' => 6,
                'tanggal' => Carbon::now()->subDays(25),
                'jumlah_terjual' => 45,
                'harga_satuan' => 4000,
                'biaya_per_unit' => 1500,
            ],
            [
                'product_id' => 6,
                'tanggal' => Carbon::now()->subDays(20),
                'jumlah_terjual' => 50,
                'harga_satuan' => 4000,
                'biaya_per_unit' => 1500,
            ],
            [
                'product_id' => 6,
                'tanggal' => Carbon::now()->subDays(15),
                'jumlah_terjual' => 55,
                'harga_satuan' => 4000,
                'biaya_per_unit' => 1500,
            ],
            [
                'product_id' => 6,
                'tanggal' => Carbon::now()->subDays(10),
                'jumlah_terjual' => 60,
                'harga_satuan' => 4000,
                'biaya_per_unit' => 1500,
            ],
            [
                'product_id' => 6,
                'tanggal' => Carbon::now()->subDays(5),
                'jumlah_terjual' => 65,
                'harga_satuan' => 4000,
                'biaya_per_unit' => 1500,
            ],
            [
                'product_id' => 6,
                'tanggal' => Carbon::now()->subDays(1),
                'jumlah_terjual' => 70,
                'harga_satuan' => 4000,
                'biaya_per_unit' => 1500,
            ],
        ];

        foreach ($incomes as $incomeData) {
            $user->incomes()->create(array_merge($incomeData, ['user_id' => $user->id]));
        }

        // Buat utang
        $debts = [
            [
                'creditor_name' => 'Supplier Daging Sapi',
                'description' => 'Utang pembelian daging sapi untuk bakso',
                'amount' => 1500000,
                'due_date' => Carbon::now()->addDays(15),
                'status' => 'unpaid',
                'paid_amount' => 0,
                'paid_date' => null,
                'notes' => 'Daging sapi premium untuk bakso',
            ],
            [
                'creditor_name' => 'Supplier Bumbu',
                'description' => 'Utang pembelian bumbu dan rempah',
                'amount' => 500000,
                'due_date' => Carbon::now()->addDays(7),
                'status' => 'partial',
                'paid_amount' => 200000,
                'paid_date' => Carbon::now()->subDays(2),
                'notes' => 'Bumbu untuk bakso dan mie ayam',
            ],
            [
                'creditor_name' => 'Supplier Sayuran',
                'description' => 'Utang pembelian sayuran segar',
                'amount' => 300000,
                'due_date' => Carbon::now()->subDays(5),
                'status' => 'unpaid',
                'paid_amount' => 0,
                'paid_date' => null,
                'notes' => 'Sawi, tauge, dan sayuran lainnya',
            ],
        ];

        foreach ($debts as $debtData) {
            $user->debts()->create($debtData);
        }

        // Buat piutang
        $receivables = [
            [
                'debtor_name' => 'Warung Kopi Pak Budi',
                'description' => 'Piutang pengiriman bakso dan mie ayam',
                'amount' => 800000,
                'due_date' => Carbon::now()->addDays(10),
                'status' => 'partial',
                'paid_amount' => 400000,
                'paid_date' => Carbon::now()->subDays(3),
                'notes' => 'Pengiriman rutin setiap hari',
            ],
            [
                'debtor_name' => 'Kantor PT Maju Bersama',
                'description' => 'Piutang catering makan siang',
                'amount' => 1200000,
                'due_date' => Carbon::now()->addDays(20),
                'status' => 'unpaid',
                'paid_amount' => 0,
                'paid_date' => null,
                'notes' => 'Catering untuk 50 orang karyawan',
            ],
            [
                'debtor_name' => 'Sekolah SD Harapan Bangsa',
                'description' => 'Piutang catering makan siang siswa',
                'amount' => 600000,
                'due_date' => Carbon::now()->subDays(3),
                'status' => 'unpaid',
                'paid_amount' => 0,
                'paid_date' => null,
                'notes' => 'Catering untuk 30 siswa kelas 6',
            ],
        ];

        foreach ($receivables as $receivableData) {
            $user->receivables()->create($receivableData);
        }

        // Buat riwayat stok
        $stockHistories = [
            // Stok Masuk
            [
                'product_id' => 1,
                'type' => 'initial',
                'quantity_change' => 100,
                'quantity_before' => 0,
                'quantity_after' => 100,
                'description' => 'Stok awal bakso sapi',
                'reference_type' => 'initial',
                'reference_id' => null,
            ],
            [
                'product_id' => 2,
                'type' => 'initial',
                'quantity_change' => 80,
                'quantity_before' => 0,
                'quantity_after' => 80,
                'description' => 'Stok awal bakso ayam',
                'reference_type' => 'initial',
                'reference_id' => null,
            ],
            [
                'product_id' => 3,
                'type' => 'initial',
                'quantity_change' => 120,
                'quantity_before' => 0,
                'quantity_after' => 120,
                'description' => 'Stok awal mie ayam',
                'reference_type' => 'initial',
                'reference_id' => null,
            ],
            [
                'product_id' => 4,
                'type' => 'initial',
                'quantity_change' => 90,
                'quantity_before' => 0,
                'quantity_after' => 90,
                'description' => 'Stok awal mie ayam bakso',
                'reference_type' => 'initial',
                'reference_id' => null,
            ],
            [
                'product_id' => 5,
                'type' => 'initial',
                'quantity_change' => 200,
                'quantity_before' => 0,
                'quantity_after' => 200,
                'description' => 'Stok awal es teh manis',
                'reference_type' => 'initial',
                'reference_id' => null,
            ],
            [
                'product_id' => 6,
                'type' => 'initial',
                'quantity_change' => 150,
                'quantity_before' => 0,
                'quantity_after' => 150,
                'description' => 'Stok awal es jeruk',
                'reference_type' => 'initial',
                'reference_id' => null,
            ],
            // Stok Keluar (penjualan)
            [
                'product_id' => 1,
                'type' => 'out',
                'quantity_change' => -25,
                'quantity_before' => 100,
                'quantity_after' => 75,
                'description' => 'Penjualan hari ini',
                'reference_type' => 'income',
                'reference_id' => 1,
            ],
            [
                'product_id' => 2,
                'type' => 'out',
                'quantity_change' => -20,
                'quantity_before' => 80,
                'quantity_after' => 60,
                'description' => 'Penjualan hari ini',
                'reference_type' => 'income',
                'reference_id' => 8,
            ],
            [
                'product_id' => 3,
                'type' => 'out',
                'quantity_change' => -30,
                'quantity_before' => 120,
                'quantity_after' => 90,
                'description' => 'Penjualan hari ini',
                'reference_type' => 'income',
                'reference_id' => 15,
            ],
            [
                'product_id' => 4,
                'type' => 'out',
                'quantity_change' => -25,
                'quantity_before' => 90,
                'quantity_after' => 65,
                'description' => 'Penjualan hari ini',
                'reference_type' => 'income',
                'reference_id' => 22,
            ],
            [
                'product_id' => 5,
                'type' => 'out',
                'quantity_change' => -50,
                'quantity_before' => 200,
                'quantity_after' => 150,
                'description' => 'Penjualan hari ini',
                'reference_type' => 'income',
                'reference_id' => 29,
            ],
            [
                'product_id' => 6,
                'type' => 'out',
                'quantity_change' => -40,
                'quantity_before' => 150,
                'quantity_after' => 110,
                'description' => 'Penjualan hari ini',
                'reference_type' => 'income',
                'reference_id' => 36,
            ],
        ];

        foreach ($stockHistories as $stockHistoryData) {
            $user->stockHistories()->create($stockHistoryData);
        }

        $this->command->info('Data dummy untuk usaha bakso dan mie ayam berhasil dibuat!');
        $this->command->info('User: ahmad@bakso.com');
        $this->command->info('Password: password123');
    }
}
