<?php

namespace App\Livewire;

use App\Models\StockHistory;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class StockHistoryList extends Component
{
    use WithPagination;

    public $selectedProductId = null;
    public $selectedType = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $showFilters = false;

    protected $queryString = [
        'selectedProductId' => ['except' => ''],
        'selectedType' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
    ];

    public function mount()
    {
        // Set default date range to last 30 days
        $this->dateFrom = now()->subDays(30)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function render()
    {
        // Get products for filter
        $products = Product::forCurrentUser()->orderBy('name')->get();

        $query = StockHistory::with(['product', 'user']);

        // Filter by product
        if ($this->selectedProductId) {
            $query->where('product_id', $this->selectedProductId);
        }

        // Filter by type
        if ($this->selectedType) {
            $query->where('type', $this->selectedType);
        }

        // Filter by date range
        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        $histories = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get summary statistics
        $summary = $this->getSummaryStatistics($query);

        // Get selected product info
        $selectedProduct = null;
        if ($this->selectedProductId) {
            $selectedProduct = $products->find($this->selectedProductId);
        }

        return view('livewire.stock-history-list', [
            'histories' => $histories,
            'products' => $products,
            'summary' => $summary,
            'selectedProduct' => $selectedProduct,
        ]);
    }

    public function getSummaryStatistics($query)
    {
        $clone = clone $query;
        $histories = $clone->get();

        return [
            'total_entries' => $histories->count(),
            'total_in' => $histories->where('type', 'in')->sum('quantity_change'),
            'total_out' => abs($histories->where('type', 'out')->sum('quantity_change')),
            'total_adjustment' => $histories->where('type', 'adjustment')->sum('quantity_change'),
            'net_change' => $histories->sum('quantity_change'),
        ];
    }

    public function clearFilters()
    {
        $this->selectedProductId = null;
        $this->selectedType = '';
        $this->dateFrom = now()->subDays(30)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
        $this->resetPage();
    }

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    public function updatedSelectedProductId()
    {
        $this->resetPage();
    }

    public function updatedSelectedType()
    {
        $this->resetPage();
    }

    public function updatedDateFrom()
    {
        $this->resetPage();
    }

    public function updatedDateTo()
    {
        $this->resetPage();
    }
}
