<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Income;
use App\Models\Expenditure;
use Carbon\Carbon;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $userId = 1;
        $produkList = ['Bakso', 'Mie Ayam'];

        // Mulai dari awal 2025 sampai akhir 2028 (4 tahun)
        $startDate = Carbon::create(2025, 1, 1);
        $endDate = Carbon::create(2028, 12, 31);

        while ($startDate->lte($endDate)) {
            // Income (penjualan bakso & mie ayam)
            foreach ($produkList as $produk) {
                Income::create([
                    'user_id' => $userId,
                    'tanggal' => $startDate->toDateString(),
                    'produk' => $produk,
                    'jumlah_terjual' => rand(5, 50), // porsi terjual
                    'harga_satuan' => $produk === 'Bakso' ? 15000 : 12000,
                    'biaya_per_unit' => $produk === 'Bakso' ? 11000 : 8000,
                ]);
            }

            // Expenditure (pengeluaran harian)
            $pengeluaranList = [
                'Beli Daging Sapi',
                'Beli Tepung & Bumbu',
                'Beli Gas Elpiji',
                'Beli Sayuran',
                'Beli Minyak Goreng',
                'Beli Mie Mentah'
            ];

            $jumlahExpenditure = rand(1, 3);
            for ($i = 0; $i < $jumlahExpenditure; $i++) {
                Expenditure::create([
                    'user_id' => $userId,
                    'tanggal' => $startDate->toDateString(),
                    'keterangan' => $pengeluaranList[array_rand($pengeluaranList)],
                    'jumlah' => rand(20000, 150000),
                ]);
            }

            

            $startDate->addDay();
        }
    }
}
