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

        $query = Income::where('user_id', Auth::id());

        if ($this->filterMonth && $this->filterYear) {
            $query->whereMonth('tanggal', $this->filterMonth)
                  ->whereYear('tanggal', $this->filterYear);
        }

        // Top 5 Best-Selling Products
        $topSelling = (clone $query)
            ->select('produk', DB::raw('SUM(jumlah_terjual) as total_terjual'))
            ->groupBy('produk')
            ->orderByDesc('total_terjual')
            ->limit(5)
            ->get();

        // Top 5 Most Profitable Products
        $topProfitable = (clone $query)
            ->select('produk', DB::raw('SUM(laba) as total_laba'))
            ->groupBy('produk')
            ->orderByDesc('total_laba')
            ->limit(5)
            ->get();

        $combined = [];

        foreach ($topSelling as $product) {
            $combined[$product->produk]['total_terjual'] = $product->total_terjual;
            $combined[$product->produk]['categories'][] = 'terlaris';
        }

        foreach ($topProfitable as $product) {
            $combined[$product->produk]['total_laba'] = $product->total_laba;
            $combined[$product->produk]['categories'][] = 'menguntungkan';
        }
        
        // Fetch the missing metric for each product
        foreach ($combined as $produk => $data) {
            if (!isset($data['total_laba'])) {
                $laba = (clone $query)->where('produk', $produk)->sum('laba');
                $combined[$produk]['total_laba'] = $laba;
            }
            if (!isset($data['total_terjual'])) {
                $terjual = (clone $query)->where('produk', $produk)->sum('jumlah_terjual');
                $combined[$produk]['total_terjual'] = $terjual;
            }
        }

        $this->analyzedProducts = $combined;
    }
}