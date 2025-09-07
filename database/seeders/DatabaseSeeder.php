<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Income;
use App\Models\Expenditure;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // User dummy
        User::factory()->create([
            'name' => 'Admin Test',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'), // login password
        ]);

        // Income dummy
        Income::factory(15)->create();

        // Expenditure dummy
        Expenditure::factory(10)->create();
        $this->call(DummyDataSeeder::class);
        
        // Seeder untuk usaha Bakso & Mie Ayam
        $this->call(BaksoMieAyamSeeder::class);
        
        // Seeder untuk data detail tambahan
        $this->call(DetailedBaksoSeeder::class);
        
        // Seeder untuk data realistis dan musiman
        $this->call(RealisticBaksoSeeder::class);
    }
}
