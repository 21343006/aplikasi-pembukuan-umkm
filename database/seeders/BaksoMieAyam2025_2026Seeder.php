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

class BaksoMieAyam2025_2026Seeder extends Seeder
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
                'quantity' => 150,
                'low_stock_threshold' => 30,
            ],
            [
                'name' => 'Bakso Ayam',
                'quantity' => 120,
                'low_stock_threshold' => 25,
            ],
            [
                'name' => 'Mie Ayam',
                'quantity' => 180,
                'low_stock_threshold' => 35,
            ],
            [
                'name' => 'Mie Ayam Bakso',
                'quantity' => 140,
                'low_stock_threshold' => 30,
            ],
            [
                'name' => 'Es Teh Manis',
                'quantity' => 300,
                'low_stock_threshold' => 50,
            ],
            [
                'name' => 'Es Jeruk',
                'quantity' => 250,
                'low_stock_threshold' => 40,
            ],
            [
                'name' => 'Bakso Urat',
                'quantity' => 80,
                'low_stock_threshold' => 20,
            ],
            [
                'name' => 'Mie Goreng',
                'quantity' => 100,
                'low_stock_threshold' => 25,
            ],
        ];

        foreach ($products as $productData) {
            Product::create(array_merge($productData, ['user_id' => $user->id]));
        }

        // Buat modal awal
        $user->capitalearly()->create([
            'tanggal_input' => Carbon::create(2025, 1, 15),
            'modal_awal' => 5000000,
        ]);

        // Buat modal masuk dan keluar untuk 2025-2026
        $capitals = [
            // 2025 - Modal Masuk
            [
                'tanggal' => Carbon::create(2025, 2, 15),
                'keperluan' => 'Pinjaman Bank',
                'keterangan' => 'Pinjaman untuk pembelian peralatan dapur',
                'nominal' => 3000000,
                'jenis' => 'masuk',
            ],
            [
                'tanggal' => Carbon::create(2025, 4, 10),
                'keperluan' => 'Investasi Teman',
                'keterangan' => 'Investasi dari teman untuk ekspansi',
                'nominal' => 2000000,
                'jenis' => 'masuk',
            ],
            [
                'tanggal' => Carbon::create(2025, 8, 20),
                'keperluan' => 'Pinjaman Koperasi',
                'keterangan' => 'Pinjaman untuk renovasi warung',
                'nominal' => 1500000,
                'jenis' => 'masuk',
            ],
            [
                'tanggal' => Carbon::create(2026, 1, 15),
                'keperluan' => 'Investasi Keluarga',
                'keterangan' => 'Investasi keluarga untuk pembelian motor pengiriman',
                'nominal' => 2500000,
                'jenis' => 'masuk',
            ],
            [
                'tanggal' => Carbon::create(2026, 6, 10),
                'keperluan' => 'Pinjaman Bank',
                'keterangan' => 'Pinjaman untuk ekspansi ke cabang baru',
                'nominal' => 5000000,
                'jenis' => 'masuk',
            ],
            // 2025-2026 - Modal Keluar
            [
                'tanggal' => Carbon::create(2025, 2, 20),
                'keperluan' => 'Pembelian Peralatan Dapur',
                'keterangan' => 'Kompor, panci, wajan, dan peralatan masak lainnya',
                'nominal' => 2500000,
                'jenis' => 'keluar',
            ],
            [
                'tanggal' => Carbon::create(2025, 4, 15),
                'keperluan' => 'Renovasi Warung',
                'keterangan' => 'Cat ulang, perbaikan atap, dan dekorasi',
                'nominal' => 1500000,
                'jenis' => 'keluar',
            ],
            [
                'tanggal' => Carbon::create(2025, 8, 25),
                'keperluan' => 'Pembelian AC dan Kipas',
                'keterangan' => 'AC 1 PK dan 2 kipas angin untuk kenyamanan pelanggan',
                'nominal' => 2000000,
                'jenis' => 'keluar',
            ],
            [
                'tanggal' => Carbon::create(2026, 1, 20),
                'keperluan' => 'Pembelian Motor Pengiriman',
                'keterangan' => 'Motor untuk layanan delivery dan pengiriman catering',
                'nominal' => 3000000,
                'jenis' => 'keluar',
            ],
            [
                'tanggal' => Carbon::create(2026, 6, 15),
                'keperluan' => 'Renovasi Cabang Baru',
                'keterangan' => 'Renovasi lokasi cabang baru di area perkantoran',
                'nominal' => 4000000,
                'jenis' => 'keluar',
            ],
        ];

        foreach ($capitals as $capitalData) {
            $user->capitals()->create($capitalData);
        }

        // Buat biaya tetap untuk 2025-2026
        $fixedCosts = [];
        $startDate = Carbon::create(2025, 1, 1);
        $endDate = Carbon::create(2026, 12, 31);
        
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addMonth()) {
            // Sewa tempat (naik 10% per tahun)
            $sewaTempat = $date->year == 2025 ? 1500000 : 1650000;
            $fixedCosts[] = [
                'tanggal' => $date->copy(),
                'keperluan' => 'Sewa Tempat',
                'nominal' => $sewaTempat,
            ];
            
            // Gaji karyawan (naik 5% per tahun)
            $gajiKaryawan = $date->year == 2025 ? 1200000 : 1260000;
            $fixedCosts[] = [
                'tanggal' => $date->copy(),
                'keperluan' => 'Gaji Karyawan',
                'nominal' => $gajiKaryawan,
            ];
            
            // Listrik & Air (bervariasi)
            $listrikAir = rand(300000, 450000);
            $fixedCosts[] = [
                'tanggal' => $date->copy(),
                'keperluan' => 'Listrik & Air',
                'nominal' => $listrikAir,
            ];
            
            // Internet (mulai dari bulan ke-6 2025)
            if ($date->gte(Carbon::create(2025, 6, 1))) {
                $fixedCosts[] = [
                    'tanggal' => $date->copy(),
                    'keperluan' => 'Internet & WiFi',
                    'nominal' => 200000,
                ];
            }
            
            // Asuransi (mulai dari bulan ke-9 2025)
            if ($date->gte(Carbon::create(2025, 9, 1))) {
                $fixedCosts[] = [
                    'tanggal' => $date->copy(),
                    'keperluan' => 'Asuransi Usaha',
                    'nominal' => 150000,
                ];
            }
        }

        foreach ($fixedCosts as $fixedCostData) {
            $user->fixedCosts()->create($fixedCostData);
        }

        // Buat pengeluaran untuk 2025-2026
        $expenditures = [];
        $startDate = Carbon::create(2025, 1, 1);
        $endDate = Carbon::create(2026, 12, 31);
        
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            // Skip hari Minggu (tidak buka)
            if ($date->dayOfWeek == 0) continue;
            
            // Pengeluaran bahan baku harian
            $dagingSapi = rand(600000, 900000);
            $dagingAyam = rand(400000, 600000);
            $mieBasah = rand(150000, 250000);
            $bumbuRempah = rand(100000, 200000);
            $sayuran = rand(80000, 150000);
            $esBatu = rand(60000, 120000);
            
            $expenditures[] = [
                'tanggal' => $date->copy(),
                'keterangan' => 'Beli daging sapi untuk bakso',
                'jumlah' => $dagingSapi,
            ];
            
            $expenditures[] = [
                'tanggal' => $date->copy(),
                'keterangan' => 'Beli daging ayam',
                'jumlah' => $dagingAyam,
            ];
            
            $expenditures[] = [
                'tanggal' => $date->copy(),
                'keterangan' => 'Beli mie basah',
                'jumlah' => $mieBasah,
            ];
            
            $expenditures[] = [
                'tanggal' => $date->copy(),
                'keterangan' => 'Beli bumbu dan rempah',
                'jumlah' => $bumbuRempah,
            ];
            
            $expenditures[] = [
                'tanggal' => $date->copy(),
                'keterangan' => 'Beli sayuran (sawi, tauge)',
                'jumlah' => $sayuran,
            ];
            
            $expenditures[] = [
                'tanggal' => $date->copy(),
                'keterangan' => 'Beli es batu dan minuman',
                'jumlah' => $esBatu,
            ];
            
            // Pengeluaran khusus bulanan
            if ($date->day == 1) {
                $expenditures[] = [
                    'tanggal' => $date->copy(),
                    'keterangan' => 'Beli kemasan dan plastik',
                    'jumlah' => rand(50000, 100000),
                ];
                
                $expenditures[] = [
                    'tanggal' => $date->copy(),
                    'keterangan' => 'Beli seragam karyawan',
                    'jumlah' => rand(100000, 200000),
                ];
            }
            
            // Pengeluaran khusus triwulan
            if ($date->day == 1 && in_array($date->month, [1, 4, 7, 10])) {
                $expenditures[] = [
                    'tanggal' => $date->copy(),
                    'keterangan' => 'Maintenance peralatan dapur',
                    'jumlah' => rand(200000, 400000),
                ];
            }
        }

        foreach ($expenditures as $expenditureData) {
            $user->expenditures()->create($expenditureData);
        }

        // Buat pemasukan untuk 2025-2026
        $incomes = [];
        $startDate = Carbon::create(2025, 1, 1);
        $endDate = Carbon::create(2026, 12, 31);
        
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            // Skip hari Minggu (tidak buka)
            if ($date->dayOfWeek == 0) continue;
            
            // Faktor musiman (penjualan lebih tinggi di akhir pekan)
            $weekendFactor = in_array($date->dayOfWeek, [5, 6]) ? 1.3 : 1.0;
            
            // Faktor bulanan (penjualan lebih tinggi di bulan tertentu)
            $monthlyFactor = 1.0;
            if (in_array($date->month, [12, 1, 2])) $monthlyFactor = 1.2; // Desember-Januari-Februari
            if (in_array($date->month, [6, 7, 8])) $monthlyFactor = 1.1; // Juni-Juli-Agustus
            
            // Bakso Sapi
            $jumlahTerjual = (int)(rand(25, 45) * $weekendFactor * $monthlyFactor);
            $incomes[] = [
                'product_id' => 1,
                'tanggal' => $date->copy(),
                'jumlah_terjual' => $jumlahTerjual,
                'harga_satuan' => 15000,
                'biaya_per_unit' => 8000,
            ];
            
            // Bakso Ayam
            $jumlahTerjual = (int)(rand(20, 40) * $weekendFactor * $monthlyFactor);
            $incomes[] = [
                'product_id' => 2,
                'tanggal' => $date->copy(),
                'jumlah_terjual' => $jumlahTerjual,
                'harga_satuan' => 12000,
                'biaya_per_unit' => 6000,
            ];
            
            // Mie Ayam
            $jumlahTerjual = (int)(rand(30, 50) * $weekendFactor * $monthlyFactor);
            $incomes[] = [
                'product_id' => 3,
                'tanggal' => $date->copy(),
                'jumlah_terjual' => $jumlahTerjual,
                'harga_satuan' => 18000,
                'biaya_per_unit' => 9000,
            ];
            
            // Mie Ayam Bakso
            $jumlahTerjual = (int)(rand(25, 45) * $weekendFactor * $monthlyFactor);
            $incomes[] = [
                'product_id' => 4,
                'tanggal' => $date->copy(),
                'jumlah_terjual' => $jumlahTerjual,
                'harga_satuan' => 22000,
                'biaya_per_unit' => 11000,
            ];
            
            // Es Teh Manis
            $jumlahTerjual = (int)(rand(50, 100) * $weekendFactor * $monthlyFactor);
            $incomes[] = [
                'product_id' => 5,
                'tanggal' => $date->copy(),
                'jumlah_terjual' => $jumlahTerjual,
                'harga_satuan' => 3000,
                'biaya_per_unit' => 1000,
            ];
            
            // Es Jeruk
            $jumlahTerjual = (int)(rand(40, 80) * $weekendFactor * $monthlyFactor);
            $incomes[] = [
                'product_id' => 6,
                'tanggal' => $date->copy(),
                'jumlah_terjual' => $jumlahTerjual,
                'harga_satuan' => 4000,
                'biaya_per_unit' => 1500,
            ];
            
            // Bakso Urat (produk baru 2026)
            if ($date->year >= 2026) {
                $jumlahTerjual = (int)(rand(15, 30) * $weekendFactor * $monthlyFactor);
                $incomes[] = [
                    'product_id' => 7,
                    'tanggal' => $date->copy(),
                    'jumlah_terjual' => $jumlahTerjual,
                    'harga_satuan' => 18000,
                    'biaya_per_unit' => 9000,
                ];
            }
            
            // Mie Goreng (produk baru 2026)
            if ($date->year >= 2026) {
                $jumlahTerjual = (int)(rand(20, 35) * $weekendFactor * $monthlyFactor);
                $incomes[] = [
                    'product_id' => 8,
                    'tanggal' => $date->copy(),
                    'jumlah_terjual' => $jumlahTerjual,
                    'harga_satuan' => 20000,
                    'biaya_per_unit' => 10000,
                ];
            }
        }

        foreach ($incomes as $incomeData) {
            $user->incomes()->create(array_merge($incomeData, ['user_id' => $user->id]));
        }

        // Buat utang untuk 2025-2026
        $debts = [
            // 2025
            [
                'creditor_name' => 'Supplier Daging Sapi Premium',
                'description' => 'Utang pembelian daging sapi premium untuk bakso',
                'amount' => 2500000,
                'due_date' => Carbon::create(2025, 3, 15),
                'status' => 'paid',
                'paid_amount' => 2500000,
                'paid_date' => Carbon::create(2025, 3, 10),
                'notes' => 'Daging sapi premium untuk bakso',
            ],
            [
                'creditor_name' => 'Supplier Bumbu & Rempah',
                'description' => 'Utang pembelian bumbu dan rempah',
                'amount' => 800000,
                'due_date' => Carbon::create(2025, 5, 20),
                'status' => 'partial',
                'paid_amount' => 500000,
                'paid_date' => Carbon::create(2025, 5, 15),
                'notes' => 'Bumbu untuk bakso dan mie ayam',
            ],
            [
                'creditor_name' => 'Supplier Sayuran Segar',
                'description' => 'Utang pembelian sayuran segar',
                'amount' => 400000,
                'due_date' => Carbon::create(2025, 7, 10),
                'status' => 'unpaid',
                'paid_amount' => 0,
                'paid_date' => null,
                'notes' => 'Sawi, tauge, dan sayuran lainnya',
            ],
            [
                'creditor_name' => 'Supplier Mie Basah',
                'description' => 'Utang pembelian mie basah',
                'amount' => 600000,
                'due_date' => Carbon::create(2025, 9, 25),
                'status' => 'paid',
                'paid_amount' => 600000,
                'paid_date' => Carbon::create(2025, 9, 20),
                'notes' => 'Mie basah untuk mie ayam dan mie goreng',
            ],
            // 2026
            [
                'creditor_name' => 'Supplier Daging Sapi Premium',
                'description' => 'Utang pembelian daging sapi premium untuk bakso',
                'amount' => 3000000,
                'due_date' => Carbon::create(2026, 2, 15),
                'status' => 'partial',
                'paid_amount' => 1500000,
                'paid_date' => Carbon::create(2026, 2, 10),
                'notes' => 'Daging sapi premium untuk bakso',
            ],
            [
                'creditor_name' => 'Supplier Peralatan Dapur',
                'description' => 'Utang pembelian peralatan dapur baru',
                'amount' => 1200000,
                'due_date' => Carbon::create(2026, 4, 30),
                'status' => 'unpaid',
                'paid_amount' => 0,
                'paid_date' => null,
                'notes' => 'Panci, wajan, dan peralatan masak tambahan',
            ],
            [
                'creditor_name' => 'Supplier Kemasan',
                'description' => 'Utang pembelian kemasan dan plastik',
                'amount' => 300000,
                'due_date' => Carbon::create(2026, 6, 15),
                'status' => 'unpaid',
                'paid_amount' => 0,
                'paid_date' => null,
                'notes' => 'Kemasan untuk delivery dan take away',
            ],
        ];

        foreach ($debts as $debtData) {
            $user->debts()->create($debtData);
        }

        // Buat piutang untuk 2025-2026
        $receivables = [
            // 2025
            [
                'debtor_name' => 'Warung Kopi Pak Budi',
                'description' => 'Piutang pengiriman bakso dan mie ayam',
                'amount' => 1200000,
                'due_date' => Carbon::create(2025, 3, 10),
                'status' => 'paid',
                'paid_amount' => 1200000,
                'paid_date' => Carbon::create(2025, 3, 8),
                'notes' => 'Pengiriman rutin setiap hari',
            ],
            [
                'debtor_name' => 'Kantor PT Maju Bersama',
                'description' => 'Piutang catering makan siang',
                'amount' => 1800000,
                'due_date' => Carbon::create(2025, 4, 20),
                'status' => 'partial',
                'paid_amount' => 900000,
                'paid_date' => Carbon::create(2025, 4, 15),
                'notes' => 'Catering untuk 60 orang karyawan',
            ],
            [
                'debtor_name' => 'Sekolah SD Harapan Bangsa',
                'description' => 'Piutang catering makan siang siswa',
                'amount' => 900000,
                'due_date' => Carbon::create(2025, 6, 15),
                'status' => 'paid',
                'paid_amount' => 900000,
                'paid_date' => Carbon::create(2025, 6, 10),
                'notes' => 'Catering untuk 40 siswa kelas 6',
            ],
            [
                'debtor_name' => 'Kantor PT Sukses Mandiri',
                'description' => 'Piutang catering rapat',
                'amount' => 600000,
                'due_date' => Carbon::create(2025, 8, 30),
                'status' => 'unpaid',
                'paid_amount' => 0,
                'paid_date' => null,
                'notes' => 'Catering untuk rapat 30 orang',
            ],
            // 2026
            [
                'debtor_name' => 'Warung Kopi Pak Budi',
                'description' => 'Piutang pengiriman bakso dan mie ayam',
                'amount' => 1500000,
                'due_date' => Carbon::create(2026, 2, 10),
                'status' => 'partial',
                'paid_amount' => 750000,
                'paid_date' => Carbon::create(2026, 2, 5),
                'notes' => 'Pengiriman rutin setiap hari',
            ],
            [
                'debtor_name' => 'Kantor PT Maju Bersama',
                'description' => 'Piutang catering makan siang',
                'amount' => 2400000,
                'due_date' => Carbon::create(2026, 3, 25),
                'status' => 'unpaid',
                'paid_amount' => 0,
                'paid_date' => null,
                'notes' => 'Catering untuk 80 orang karyawan',
            ],
            [
                'debtor_name' => 'Sekolah SMP Cerdas',
                'description' => 'Piutang catering makan siang siswa',
                'amount' => 1200000,
                'due_date' => Carbon::create(2026, 5, 20),
                'status' => 'unpaid',
                'paid_amount' => 0,
                'paid_date' => null,
                'notes' => 'Catering untuk 50 siswa kelas 8',
            ],
            [
                'debtor_name' => 'Hotel Grand City',
                'description' => 'Piutang catering tamu hotel',
                'amount' => 3000000,
                'due_date' => Carbon::create(2026, 7, 15),
                'status' => 'unpaid',
                'paid_amount' => 0,
                'paid_date' => null,
                'notes' => 'Catering untuk 100 tamu hotel',
            ],
        ];

        foreach ($receivables as $receivableData) {
            $user->receivables()->create($receivableData);
        }

        // Buat riwayat stok untuk 2025-2026
        $stockHistories = [
            // Stok Awal 2025
            [
                'product_id' => 1,
                'type' => 'initial',
                'quantity_change' => 150,
                'quantity_before' => 0,
                'quantity_after' => 150,
                'description' => 'Stok awal bakso sapi 2025',
                'reference_type' => 'initial',
                'reference_id' => null,
            ],
            [
                'product_id' => 2,
                'type' => 'initial',
                'quantity_change' => 120,
                'quantity_before' => 0,
                'quantity_after' => 120,
                'description' => 'Stok awal bakso ayam 2025',
                'reference_type' => 'initial',
                'reference_id' => null,
            ],
            [
                'product_id' => 3,
                'type' => 'initial',
                'quantity_change' => 180,
                'quantity_before' => 0,
                'quantity_after' => 180,
                'description' => 'Stok awal mie ayam 2025',
                'reference_type' => 'initial',
                'reference_id' => null,
            ],
            [
                'product_id' => 4,
                'type' => 'initial',
                'quantity_change' => 140,
                'quantity_before' => 0,
                'quantity_after' => 140,
                'description' => 'Stok awal mie ayam bakso 2025',
                'reference_type' => 'initial',
                'reference_id' => null,
            ],
            [
                'product_id' => 5,
                'type' => 'initial',
                'quantity_change' => 300,
                'quantity_before' => 0,
                'quantity_after' => 300,
                'description' => 'Stok awal es teh manis 2025',
                'reference_type' => 'initial',
                'reference_id' => null,
            ],
            [
                'product_id' => 6,
                'type' => 'initial',
                'quantity_change' => 250,
                'quantity_before' => 0,
                'quantity_after' => 250,
                'description' => 'Stok awal es jeruk 2025',
                'reference_type' => 'initial',
                'reference_id' => null,
            ],
            // Stok Awal 2026 (produk baru)
            [
                'product_id' => 7,
                'type' => 'initial',
                'quantity_change' => 80,
                'quantity_before' => 0,
                'quantity_after' => 80,
                'description' => 'Stok awal bakso urat 2026',
                'reference_type' => 'initial',
                'reference_id' => null,
            ],
            [
                'product_id' => 8,
                'type' => 'initial',
                'quantity_change' => 100,
                'quantity_before' => 0,
                'quantity_after' => 100,
                'description' => 'Stok awal mie goreng 2026',
                'reference_type' => 'initial',
                'reference_id' => null,
            ],
        ];

        foreach ($stockHistories as $stockHistoryData) {
            $user->stockHistories()->create($stockHistoryData);
        }

        $this->command->info('Data dummy untuk usaha bakso dan mie ayam tahun 2025-2026 berhasil dibuat!');
        $this->command->info('User: ahmad@bakso.com');
        $this->command->info('Password: password123');
        $this->command->info('Periode Data: 1 Januari 2025 - 31 Desember 2026');
        $this->command->info('Total Data: ' . count($incomes) . ' transaksi pemasukan');
        $this->command->info('Total Data: ' . count($expenditures) . ' transaksi pengeluaran');
    }
}
