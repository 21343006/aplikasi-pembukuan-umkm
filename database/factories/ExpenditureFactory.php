<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Expenditure;

class ExpenditureFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tanggal' => fake()->dateTimeBetween('-1 month', 'now'),
            'keterangan' => fake()->sentence(3),
            'jumlah' => fake()->numberBetween(1, 10)
        ];
    }
}
