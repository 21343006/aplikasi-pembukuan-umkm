<?php

namespace App\Livewire\Beps;

use App\Models\Bep;
use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class BepForm extends Component
{
    #[Title('Titik Balik Keuntungan/BEP')]

    public $nama_produk, $modal_tetap, $harga_per_barang, $modal_per_barang;
    public $beploadbep = [];
    public $showModal = false;
    public $isEdit = false;
    public $bep_id;

    protected $rules = [
        'nama_produk' => 'required|string|max:255',
        'modal_tetap' => 'required|numeric|min:0',
        'harga_per_barang' => 'required|numeric|min:0',
        'modal_per_barang' => 'required|numeric|min:0',
    ];

    protected $messages = [
        'nama_produk.required' => 'Nama produk wajib diisi.',
        'nama_produk.max' => 'Nama produk maksimal 255 karakter.',
        'modal_tetap.required' => 'Modal tetap wajib diisi.',
        'modal_tetap.numeric' => 'Modal tetap harus berupa angka.',
        'modal_tetap.min' => 'Modal tetap tidak boleh kurang dari 0.',
        'harga_per_barang.required' => 'Harga per barang wajib diisi.',
        'harga_per_barang.numeric' => 'Harga per barang harus berupa angka.',
        'harga_per_barang.min' => 'Harga per barang tidak boleh kurang dari 0.',
        'modal_per_barang.required' => 'Modal per barang wajib diisi.',
        'modal_per_barang.numeric' => 'Modal per barang harus berupa angka.',
        'modal_per_barang.min' => 'Modal per barang tidak boleh kurang dari 0.',
    ];

    public function mount()
    {
        $this->loadbep();
    }

    public function loadbep()
    {
        try {
            if (!Auth::check()) {
                $this->beploadbep = collect([]);
                return;
            }

            $this->beploadbep = Bep::where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->get();
        } catch (\Exception $e) {
            Log::error('Error in loadbep method: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->beploadbep = collect([]);
            session()->flash('error', 'Terjadi kesalahan saat memuat data. Silakan coba lagi.');
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
        try {
            if (!Auth::check()) {
                session()->flash('error', 'Anda harus login terlebih dahulu.');
                return;
            }

            $this->validate();

            if ($this->harga_per_barang <= $this->modal_per_barang) {
                $this->addError('harga_per_barang', 'Harga per barang harus lebih besar dari modal per barang untuk mendapatkan keuntungan.');
                return;
            }

            $data = [
                'user_id' => Auth::id(),
                'nama_produk' => trim($this->nama_produk),
                'modal_tetap' => (float) $this->modal_tetap,
                'harga_per_barang' => (float) $this->harga_per_barang,
                'modal_per_barang' => (float) $this->modal_per_barang,
            ];

            if ($this->isEdit && $this->bep_id) {
                $bep = Bep::where('user_id', Auth::id())->findOrFail($this->bep_id);
                $bep->update($data);
                session()->flash('message', 'Data berhasil diperbarui!');
            } else {
                Bep::create($data);
                session()->flash('message', 'Data berhasil ditambahkan!');
            }

            $this->closeModal();
            $this->loadbep();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('BEP not found: ' . $e->getMessage(), ['bep_id' => $this->bep_id, 'user_id' => Auth::id()]);
            session()->flash('error', 'Data tidak ditemukan atau Anda tidak memiliki akses.');
        } catch (\Exception $e) {
            Log::error('Error in save method: ' . $e->getMessage(), [
                'data' => $data ?? null,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.');
        }
    }

    public function edit($id)
    {
        try {
            if (!Auth::check()) {
                session()->flash('error', 'Anda harus login terlebih dahulu.');
                return;
            }

            if (!is_numeric($id) || $id <= 0) {
                session()->flash('error', 'ID tidak valid.');
                return;
            }

            $bep = Bep::where('user_id', Auth::id())->findOrFail($id);
            
            $this->bep_id = $bep->id;
            $this->nama_produk = $bep->nama_produk;
            $this->modal_tetap = $bep->modal_tetap;
            $this->harga_per_barang = $bep->harga_per_barang;
            $this->modal_per_barang = $bep->modal_per_barang;
            $this->isEdit = true;
            $this->showModal = true;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('BEP not found for edit: ' . $e->getMessage(), ['id' => $id, 'user_id' => Auth::id()]);
            session()->flash('error', 'Data tidak ditemukan atau Anda tidak memiliki akses.');
        } catch (\Exception $e) {
            Log::error('Error in edit method: ' . $e->getMessage(), [
                'id' => $id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Terjadi kesalahan saat memuat data untuk diedit.');
        }
    }

    public function confirmDelete($id)
    {
        $this->delete($id);
    }

    public function delete($id)
    {
        try {
            if (!Auth::check()) {
                session()->flash('error', 'Anda harus login terlebih dahulu.');
                return;
            }

            if (!is_numeric($id) || $id <= 0) {
                session()->flash('error', 'ID tidak valid.');
                return;
            }

            $bep = Bep::where('user_id', Auth::id())->findOrFail($id);
            $bep->delete();
            
            $this->loadbep();
            session()->flash('message', 'Data berhasil dihapus!');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('BEP not found for delete: ' . $e->getMessage(), ['id' => $id, 'user_id' => Auth::id()]);
            session()->flash('error', 'Data tidak ditemukan atau Anda tidak memiliki akses.');
        } catch (\Exception $e) {
            Log::error('Error in delete method: ' . $e->getMessage(), [
                'id' => $id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Gagal menghapus data. Silakan coba lagi.');
        }
    }

    public function resetInput()
    {
        $this->reset([
            'nama_produk',
            'modal_tetap',
            'harga_per_barang',
            'modal_per_barang',
            'bep_id',
            'isEdit',
        ]);

        $this->resetValidation();
    }

    public function updatedNamaProduk()
    {
        $this->validateOnly('nama_produk');
    }

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

    public function getBepPreviewProperty()
    {
        if (!$this->modal_tetap || !$this->harga_per_barang || !$this->modal_per_barang) {
            return 0;
        }

        $keuntunganPerUnit = (float)$this->harga_per_barang - (float)$this->modal_per_barang;
        
        if ($keuntunganPerUnit <= 0) {
            return 0;
        }

        return ceil((float)$this->modal_tetap / $keuntunganPerUnit);
    }

    public function getKeuntunganPreviewProperty()
    {
        if (!$this->harga_per_barang || !$this->modal_per_barang) {
            return 0;
        }

        return (float)$this->harga_per_barang - (float)$this->modal_per_barang;
    }

    public function render()
    {
        return view('livewire.beps.bep-form');
    }
}