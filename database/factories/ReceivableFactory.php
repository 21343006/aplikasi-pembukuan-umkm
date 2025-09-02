<?php

namespace Database\Factories;

use App\Models\Receivable;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Receivable>
 */
class ReceivableFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Data yang lebih konsisten untuk UMKM makanan
        $debtors = [
            'Toko Makmur' => 'Penjualan bakso porsi besar',
            'Warung Sederhana' => 'Penjualan mie ayam harian',
            'Restoran Sejahtera' => 'Penjualan paket catering',
            'Cafe Nusantara' => 'Penjualan bakso premium',
            'Toko Kelontong' => 'Penjualan mie ayam grosir',
            'Warung Makan' => 'Penjualan bakso porsi regular',
            'Toko Elektronik' => 'Penjualan paket makan karyawan',
            'Toko Pakaian' => 'Penjualan mie ayam porsi besar',
            'Toko Sepatu' => 'Penjualan bakso porsi kecil',
            'Toko Buku' => 'Penjualan mie ayam porsi regular',
            'Toko Mainan' => 'Penjualan bakso porsi medium',
            'Toko Kue' => 'Penjualan mie ayam premium'
        ];

        $debtor = $this->faker->randomElement(array_keys($debtors));
        $description = $debtors[$debtor];

        // Jumlah piutang yang lebih realistis untuk UMKM makanan
        $amount = $this->faker->randomElement([
            25000, 50000, 75000, 100000, 125000, 150000, 200000, 250000, 300000, 400000
        ]);

        // Status yang lebih masuk akal dengan sinkronisasi sempurna
        $statusChance = $this->faker->numberBetween(1, 100);
        
        if ($statusChance <= 60) {
            // 60% unpaid - belum dibayar sama sekali
            $status = 'unpaid';
            $paidAmount = 0;
            $paidDate = null;
        } elseif ($statusChance <= 85) {
            // 25% partial - sudah dibayar sebagian
            $status = 'partial';
            $paidAmount = $this->faker->randomElement([
                round($amount * 0.3), 
                round($amount * 0.5), 
                round($amount * 0.7)
            ]);
            $paidDate = $this->faker->dateTimeBetween('-20 days', 'now');
        } else {
            // 15% paid - sudah lunas
            $status = 'paid';
            $paidAmount = $amount; // Harus sama dengan amount untuk status paid
            $paidDate = $this->faker->dateTimeBetween('-45 days', '-1 day');
        }

        // Tanggal jatuh tempo yang lebih realistis
        $dueDate = $this->faker->dateTimeBetween('now', '+45 days');

        // Catatan yang lebih relevan
        $notes = null;
        if ($status === 'partial') {
            $percentage = round(($paidAmount / $amount) * 100);
            $notes = "Pembayaran DP {$percentage}%";
        } elseif ($status === 'paid') {
            $notes = 'Lunas dibayar tepat waktu';
        } elseif ($this->faker->boolean(25)) {
            $notes = 'Perlu follow up pembayaran';
        }

        return [
            'user_id' => User::factory(),
            'debtor_name' => $debtor,
            'description' => $description,
            'amount' => $amount,
            'due_date' => $dueDate,
            'status' => $status,
            'paid_amount' => $paidAmount,
            'paid_date' => $paidDate,
            'notes' => $notes,
        ];
    }
}
