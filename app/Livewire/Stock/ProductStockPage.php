<?php

namespace App\Livewire\Stock;

use Livewire\Component;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class ProductStockPage extends Component
{
    use WithPagination;

    public $name, $quantity, $low_stock_threshold = 10, $productId;
    public $isOpen = false;
    
    // Add Stock Modal
    public $showAddStockModal = false;
    public $selectedProduct;
    public $addQuantity;
    public $addDescription = '';
    
    // Adjust Stock Modal
    public $showAdjustStockModal = false;
    public $adjustQuantity;
    public $adjustDescription = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'quantity' => 'required|integer|min:0',
        'low_stock_threshold' => 'required|integer|min:0',
    ];

    public function render()
    {
        return view('livewire.stock.product-stock-page', [
            'products' => Product::where('user_id', Auth::id())->paginate(10),
        ]);
    }

    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function openModal()
    {
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
    }

    private function resetInputFields()
    {
        $this->name = '';
        $this->quantity = '';
        $this->low_stock_threshold = 10;
        $this->productId = null;
    }

    public function store()
    {
        $this->validate();

        Product::updateOrCreate(['id' => $this->productId], [
            'user_id' => Auth::id(),
            'name' => $this->name,
            'quantity' => $this->quantity,
            'low_stock_threshold' => $this->low_stock_threshold,
        ]);

        session()->flash('message', 
            $this->productId ? 'Produk berhasil diperbarui.' : 'Produk berhasil ditambahkan.');

        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $this->productId = $id;
        $this->name = $product->name;
        $this->quantity = $product->quantity;
        $this->low_stock_threshold = $product->low_stock_threshold;

        $this->openModal();
    }

    public function delete($id)
    {
        Product::find($id)->delete();
        session()->flash('message', 'Produk berhasil dihapus.');
    }

    // Add Stock Methods
    public function addStock($id)
    {
        $this->selectedProduct = Product::findOrFail($id);
        $this->addQuantity = '';
        $this->addDescription = '';
        $this->showAddStockModal = true;
    }

    public function closeAddStockModal()
    {
        $this->showAddStockModal = false;
        $this->selectedProduct = null;
        $this->addQuantity = '';
        $this->addDescription = '';
    }

    public function storeAddStock()
    {
        $this->validate([
            'addQuantity' => 'required|integer|min:1',
            'addDescription' => 'required|string|max:500',
        ]);

        $this->selectedProduct->addStock(
            $this->addQuantity,
            $this->addDescription
        );

        session()->flash('message', 'Stok berhasil ditambahkan.');
        $this->closeAddStockModal();
    }

    // Adjust Stock Methods
    public function adjustStock($id)
    {
        $this->selectedProduct = Product::findOrFail($id);
        $this->adjustQuantity = $this->selectedProduct->quantity;
        $this->adjustDescription = '';
        $this->showAdjustStockModal = true;
    }

    public function closeAdjustStockModal()
    {
        $this->showAdjustStockModal = false;
        $this->selectedProduct = null;
        $this->adjustQuantity = '';
        $this->adjustDescription = '';
    }

    public function storeAdjustStock()
    {
        $this->validate([
            'adjustQuantity' => 'required|integer|min:0',
            'adjustDescription' => 'required|string|max:500',
        ]);

        $this->selectedProduct->adjustStock(
            $this->adjustQuantity,
            $this->adjustDescription
        );

        session()->flash('message', 'Stok berhasil disesuaikan.');
        $this->closeAdjustStockModal();
    }
}
