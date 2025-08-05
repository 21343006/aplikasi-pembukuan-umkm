<?php

namespace App\Livewire\Beps;

use App\Models\FixedCost;
use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Log;

class BepForm extends Component
{
    #[Title('Modal Tetap & BEP')]
    
    public $nama_produk, $modal_tetap, $harga_per_barang, $modal_per_barang;
    public $fixedCosts = [];
    public $showModal = false;
    public $isEdit = false;
    public $fixed_cost_id;

    // Rules untuk validation - harus di Livewire component, bukan di Model
    protected $rules = [
        'nama_produk' => 'required|string|max:255',
        'modal_tetap' => 'required|numeric|min:0',
        'harga_per_barang' => 'required|numeric|min:0',
        'modal_per_barang' => 'required|numeric|min:0',
    ];

    public function mount()
    {
        $this->loadFixedCosts();
    }

    public function loadFixedCosts()
    {
        try {
            $this->fixedCosts = FixedCost::orderBy('created_at', 'desc')->get();
        } catch (\Exception $e) {
            $this->fixedCosts = collect([]);
            session()->flash('error', 'Terjadi kesalahan saat memuat data: ' . $e->getMessage());
        }
    }

    public function openModal()
    {
        $this->resetInput();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->resetInput();
        $this->showModal = false;
    }

    public function save()
    {
        $this->validate();

        try {
            // Validasi agar harga per barang lebih besar dari modal per barang
            if ($this->harga_per_barang <= $this->modal_per_barang) {
                $this->addError('harga_per_barang', 'Harga per barang harus lebih besar dari modal per barang untuk mendapatkan keuntungan.');
                return;
            }

            $data = [
                'nama_produk' => trim($this->nama_produk),
                'modal_tetap' => (float) $this->modal_tetap,
                'harga_per_barang' => (float) $this->harga_per_barang,
                'modal_per_barang' => (float) $this->modal_per_barang,
            ];

            if ($this->isEdit && $this->fixed_cost_id) {
                // Update data
                $fixedCost = FixedCost::findOrFail($this->fixed_cost_id);
                $fixedCost->update($data);
                session()->flash('message', 'Data berhasil diperbarui!');
            } else {
                // Create data baru
                FixedCost::create($data);
                session()->flash('message', 'Data berhasil ditambahkan!');
            }

            $this->closeModal();
            $this->loadFixedCosts();
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $fixedCost = FixedCost::findOrFail($id);

            $this->fixed_cost_id = $fixedCost->id;
            $this->nama_produk = $fixedCost->nama_produk;
            $this->modal_tetap = $fixedCost->modal_tetap;
            $this->harga_per_barang = $fixedCost->harga_per_barang;
            $this->modal_per_barang = $fixedCost->modal_per_barang;
            $this->isEdit = true;
            $this->showModal = true;
        } catch (\Exception $e) {
            session()->flash('error', 'Data tidak ditemukan atau terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function confirmDelete($id)
    {
        $this->delete($id);
    }

    public function delete($id)
    {
        try {
            $fixedCost = FixedCost::findOrFail($id);
            $fixedCost->delete();
            $this->loadFixedCosts();
            session()->flash('message', 'Data berhasil dihapus!');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    public function resetInput()
    {
        $this->reset([
            'nama_produk',
            'modal_tetap',
            'harga_per_barang',
            'modal_per_barang',
            'fixed_cost_id',
            'isEdit'
        ]);

        // Clear validation errors
        $this->resetValidation();
    }

    // Real-time calculation untuk preview BEP
    public function updatedModalTetap()
    {
        $this->validateOnly('modal_tetap');
    }

    public function updatedHargaPerBarang()
    {
        $this->validateOnly('harga_per_barang');
    }

    public function updatedModalPerBarang()
    {
        $this->validateOnly('modal_per_barang');
    }

    public function updatedNamaProduk()
    {
        $this->validateOnly('nama_produk');
    }

    // Computed property untuk preview BEP di modal
    public function getBepPreviewProperty()
    {
        if (!$this->modal_tetap || !$this->harga_per_barang || !$this->modal_per_barang) {
            return 0;
        }

        $keuntunganPerUnit = $this->harga_per_barang - $this->modal_per_barang;
        
        if ($keuntunganPerUnit <= 0) {
            return 0;
        }
        
        return ceil($this->modal_tetap / $keuntunganPerUnit);
    }

    // Computed property untuk preview keuntungan per unit
    public function getKeuntunganPreviewProperty()
    {
        if (!$this->harga_per_barang || !$this->modal_per_barang) {
            return 0;
        }

        return $this->harga_per_barang - $this->modal_per_barang;
    }

    public function render()
    {
        return view('livewire.beps.bep-form');
    }
}