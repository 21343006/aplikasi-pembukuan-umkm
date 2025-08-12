<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class IncomeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tanggal' => fake()->dateTimeBetween('-1 month', 'now'),
            'produk' => fake()->word(),
            'jumlah_terjual' => fake()->numberBetween(1, 20),
            'harga_satuan' => fake()->numberBetween(5000, 50000),
        ];
    }
}
