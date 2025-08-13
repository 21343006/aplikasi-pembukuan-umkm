<?php

namespace App\Livewire\Capitals;

use Livewire\Component;
use App\Models\Capitalearly;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CapitalearlyForm extends Component
{
    #[Title('Form Modal Awal')]
    public $modal_awal;
    public $tanggal_input;
    public $editingId = null;
    public $isEditing = false;

    protected $rules = [
        'modal_awal' => 'required|numeric|min:0',
        'tanggal_input' => 'required|date',
    ];

    protected $messages = [
        'modal_awal.required' => 'Modal awal harus diisi.',
        'modal_awal.numeric' => 'Modal awal harus berupa angka.',
        'modal_awal.min' => 'Modal awal tidak boleh kurang dari 0.',
        'tanggal_input.required' => 'Tanggal input harus diisi.',
        'tanggal_input.date' => 'Format tanggal tidak valid.',
    ];

    public function mount()
    {
        // Set default tanggal input ke hari ini
        $this->tanggal_input = Carbon::now()->format('Y-m-d');
    }

    public function save()
    {
        try {
            // Cek apakah user sudah login
            if (!Auth::check()) {
                session()->flash('error', 'Anda harus login terlebih dahulu.');
                return;
            }

            // Validasi input
            $this->validate();

            if ($this->isEditing && $this->editingId) {
                // Update data yang sedang diedit
                $capital = Capitalearly::where('user_id', Auth::id())->findOrFail($this->editingId);
                $capital->update([
                    'modal_awal' => (float) $this->modal_awal,
                    'tanggal_input' => Carbon::parse($this->tanggal_input),
                ]);
                session()->flash('success', 'Modal awal berhasil diperbarui.');
                $this->cancelEdit();
            } else {
                // Buat data baru
                Capitalearly::create([
                    'user_id' => Auth::id(),
                    'modal_awal' => (float) $this->modal_awal,
                    'tanggal_input' => Carbon::parse($this->tanggal_input),
                ]);
                session()->flash('success', 'Modal awal berhasil disimpan.');
                $this->reset('modal_awal');
                $this->tanggal_input = Carbon::now()->format('Y-m-d'); // Reset ke tanggal hari ini
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation errors akan ditangani otomatis oleh Livewire
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error in CapitalearlyForm save method: ' . $e->getMessage(), [
                'modal_awal' => $this->modal_awal,
                'tanggal_input' => $this->tanggal_input,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.');
        }
    }

    public function updatedModalAwal()
    {
        // Validasi real-time saat user mengetik
        $this->validateOnly('modal_awal');
    }

    public function updatedTanggalInput()
    {
        // Validasi real-time saat user mengubah tanggal
        $this->validateOnly('tanggal_input');
    }

    public function edit($id)
    {
        try {
            $capital = Capitalearly::where('user_id', Auth::id())->findOrFail($id);

            $this->editingId = $id;
            $this->isEditing = true;
            $this->modal_awal = $capital->modal_awal;

            // Pengecekan eksplisit untuk menghilangkan warning IDE
            $tanggalInput = $capital->tanggal_input;
            if ($tanggalInput !== null && $tanggalInput !== '') {
                $this->tanggal_input = date('Y-m-d', strtotime($capital->tanggal_input ?? 'today'));
            } else {
                $this->tanggal_input = date('Y-m-d');
            }
        } catch (\Exception $e) {
            Log::error('Error in CapitalearlyForm edit method: ' . $e->getMessage(), [
                'id' => $id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Data tidak ditemukan atau terjadi kesalahan.');
        }
    }

    public function cancelEdit()
    {
        $this->editingId = null;
        $this->isEditing = false;
        $this->reset('modal_awal');
        $this->tanggal_input = Carbon::now()->format('Y-m-d');
    }

    public function delete($id)
    {
        try {
            $capital = Capitalearly::where('user_id', Auth::id())->findOrFail($id);
            $capital->delete();

            session()->flash('success', 'Modal awal berhasil dihapus.');

            // Jika sedang mengedit data yang dihapus, cancel edit
            if ($this->isEditing && $this->editingId == $id) {
                $this->cancelEdit();
            }
        } catch (\Exception $e) {
            Log::error('Error in CapitalearlyForm delete method: ' . $e->getMessage(), [
                'id' => $id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Terjadi kesalahan saat menghapus data.');
        }
    }

    public function render()
    {
        try {
            // Cek apakah user sudah login
            if (!Auth::check()) {
                return view('livewire.capitals.capitalearly-form', [
                    'capitals' => collect([]),
                    'total_modal' => 0,
                ]);
            }

            // Ambil data capitals dengan error handling
            $capitals = Capitalearly::where('user_id', Auth::id())
                ->latest()
                ->get();

            $totalModal = Capitalearly::where('user_id', Auth::id())
                ->sum('modal_awal') ?? 0;

            return view('livewire.capitals.capitalearly-form', [
                'capitals' => $capitals,
                'total_modal' => $totalModal,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in CapitalearlyForm render method: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return safe fallback data
            return view('livewire.capitals.capitalearly-form', [
                'capitals' => collect([]),
                'total_modal' => 0,
            ]);
        }
    }
}
