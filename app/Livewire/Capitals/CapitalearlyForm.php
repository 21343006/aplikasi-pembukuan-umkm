<?php

namespace App\Livewire\Capitals;

use Livewire\Component;
use App\Models\Capitalearly;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CapitalearlyForm extends Component
{
    #[Title('Form Modal Awal')]
    public $modal_awal;

    protected $rules = [
        'modal_awal' => 'required|numeric|min:0',
    ];

    protected $messages = [
        'modal_awal.required' => 'Modal awal harus diisi.',
        'modal_awal.numeric' => 'Modal awal harus berupa angka.',
        'modal_awal.min' => 'Modal awal tidak boleh kurang dari 0.',
    ];

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

            // Buat data baru
            Capitalearly::create([
                'user_id' => Auth::id(),
                'modal_awal' => (float) $this->modal_awal,
            ]);

            session()->flash('success', 'Modal awal berhasil disimpan.');
            $this->reset('modal_awal');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation errors akan ditangani otomatis oleh Livewire
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error in CapitalearlyForm save method: ' . $e->getMessage(), [
                'modal_awal' => $this->modal_awal,
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