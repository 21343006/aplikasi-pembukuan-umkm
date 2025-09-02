<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\StockHistory;

class StockHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil semua produk yang sudah ada
        $products = Product::all();

        foreach ($products as $product) {
            // Cek apakah sudah ada riwayat stok untuk produk ini
            $existingHistory = StockHistory::where('product_id', $product->id)->first();
            
            if (!$existingHistory) {
                // Buat riwayat stok awal untuk produk yang sudah ada
                StockHistory::create([
                    'product_id' => $product->id,
                    'user_id' => $product->user_id,
                    'type' => 'initial',
                    'quantity_change' => $product->quantity,
                    'quantity_before' => 0,
                    'quantity_after' => $product->quantity,
                    'description' => 'Stok awal produk (migrasi data)',
                    'created_at' => $product->created_at,
                    'updated_at' => $product->updated_at,
                ]);
            }
        }

        $this->command->info('Stock history data has been seeded successfully!');
    }
}
