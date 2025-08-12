<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ReportharianFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => 1, // ganti sesuai user yang mau dipakai
            'tanggal' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'keterangan' => $this->faker->sentence(3),
            'uang_masuk' => $this->faker->numberBetween(50000, 500000),
            'uang_keluar' => $this->faker->numberBetween(10000, 300000),
            'saldo' => 0, // bisa dihitung di seeder
        ];
    }
}
