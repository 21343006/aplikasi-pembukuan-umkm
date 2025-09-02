<?php

namespace Database\Factories;

use App\Models\Debt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Debt>
 */
class DebtFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Data yang lebih konsisten untuk UMKM makanan
        $creditors = [
            'PT Sukses Makmur' => 'Supplier daging sapi',
            'CV Jaya Abadi' => 'Supplier tepung dan bumbu',
            'UD Maju Bersama' => 'Supplier sayuran segar',
            'Toko Bangunan Sejahtera' => 'Supplier gas elpiji',
            'Supplier Bahan Baku' => 'Supplier minyak goreng',
            'PT Mitra Usaha' => 'Supplier mie mentah',
            'CV Sumber Rejeki' => 'Supplier bumbu masak',
            'UD Makmur Jaya' => 'Supplier plastik kemasan',
            'Toko Sembako Makmur' => 'Supplier gula dan garam',
            'Supplier Elektronik' => 'Supplier peralatan dapur',
            'PT Mitra Mandiri' => 'Supplier es batu',
            'CV Berkah Abadi' => 'Supplier saus dan kecap'
        ];

        $creditor = $this->faker->randomElement(array_keys($creditors));
        $description = $creditors[$creditor];

        // Jumlah utang yang lebih realistis untuk UMKM makanan
        $amount = $this->faker->randomElement([
            50000, 75000, 100000, 125000, 150000, 200000, 250000, 300000, 400000, 500000
        ]);

        // Status yang lebih masuk akal dengan sinkronisasi sempurna
        $statusChance = $this->faker->numberBetween(1, 100);
        
        if ($statusChance <= 70) {
            // 70% unpaid - belum dibayar sama sekali
            $status = 'unpaid';
            $paidAmount = 0;
            $paidDate = null;
        } elseif ($statusChance <= 90) {
            // 20% partial - sudah dibayar sebagian
            $status = 'partial';
            $paidAmount = $this->faker->randomElement([
                round($amount * 0.25), 
                round($amount * 0.5), 
                round($amount * 0.75)
            ]);
            $paidDate = $this->faker->dateTimeBetween('-30 days', 'now');
        } else {
            // 10% paid - sudah lunas
            $status = 'paid';
            $paidAmount = $amount; // Harus sama dengan amount untuk status paid
            $paidDate = $this->faker->dateTimeBetween('-60 days', '-1 day');
        }

        // Tanggal jatuh tempo yang lebih realistis
        $dueDate = $this->faker->dateTimeBetween('now', '+60 days');

        // Catatan yang lebih relevan
        $notes = null;
        if ($status === 'partial') {
            $percentage = round(($paidAmount / $amount) * 100);
            $notes = "Pembayaran cicilan {$percentage}%";
        } elseif ($status === 'paid') {
            $notes = 'Lunas dibayar tepat waktu';
        } elseif ($this->faker->boolean(20)) {
            $notes = 'Perlu follow up pembayaran';
        }

        return [
            'user_id' => User::factory(),
            'creditor_name' => $creditor,
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
