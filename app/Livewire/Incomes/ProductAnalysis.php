<?php

namespace App\Livewire\Incomes;

use Livewire\Component;
use App\Models\Income;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;

class ProductAnalysis extends Component
{
    #[Title('Analisis Produk')]

    public $analyzedProducts = [];
    public $filterMonth = '';
    public $filterYear = '';

    protected $listeners = ['loadProductAnalysis'];

    public function mount($month = '', $year = '')
    {
        $this->filterMonth = $month ?: now()->month;
        $this->filterYear = $year ?: now()->year;
        $this->loadProductAnalysis();
    }

    public function loadProductAnalysis($filters = [])
    {
        if (isset($filters['month'])) {
            $this->filterMonth = $filters['month'];
        }
        if (isset($filters['year'])) {
            $this->filterYear = $filters['year'];
        }

        // Gunakan model dengan global scope - tidak perlu where('user_id', Auth::id())
        $query = Income::query();

        if ($this->filterMonth && $this->filterYear) {
            $query->whereMonth('tanggal', $this->filterMonth)
                  ->whereYear('tanggal', $this->filterYear);
        }

        // Get all products with their totals
        $allProducts = (clone $query)
            ->select('produk', 
                DB::raw('SUM(jumlah_terjual) as total_terjual'),
                DB::raw('SUM(laba) as total_laba')
            )
            ->groupBy('produk')
            ->having('total_terjual', '>', 0)
            ->having('total_laba', '>', 0)
            ->get();

        if ($allProducts->isEmpty()) {
            $this->analyzedProducts = [];
            return;
        }

        // Find the best-selling product (highest total_terjual)
        $bestSelling = $allProducts->sortByDesc('total_terjual')->first();
        
        // Find the most profitable product (highest total_laba)
        $mostProfitable = $allProducts->sortByDesc('total_laba')->first();

        $analyzedProducts = [];

        // Add best-selling product
        if ($bestSelling) {
            $analyzedProducts[] = [
                'name' => $bestSelling->produk,
                'total_terjual' => $bestSelling->total_terjual,
                'total_laba' => $bestSelling->total_laba,
                'categories' => ['terlaris'],
                'rank' => 1
            ];
        }

        // Add most profitable product (if different from best-selling)
        if ($mostProfitable && $mostProfitable->produk !== $bestSelling->produk) {
            $analyzedProducts[] = [
                'name' => $mostProfitable->produk,
                'total_terjual' => $mostProfitable->total_terjual,
                'total_laba' => $mostProfitable->total_laba,
                'categories' => ['menguntungkan'],
                'rank' => 2
            ];
        }

        // If the same product is both best-selling and most profitable, update its categories
        if ($bestSelling && $mostProfitable && $bestSelling->produk === $mostProfitable->produk) {
            $analyzedProducts[0]['categories'] = ['terlaris', 'menguntungkan'];
        }

        $this->analyzedProducts = $analyzedProducts;
    }
}