<?php

namespace App\Livewire\Expenditures;

use App\Models\Expenditure;
use Livewire\Attributes\Title;
use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ExpenditurePage extends Component
{
    #[Title('Biaya Pengeluaran')]
    public $tanggal, $keterangan, $jumlah;
    public array $expenditures = []; // Ubah ke array biasa
    public $total = 0;
    public $showModal = false;
    public $isEdit = false;
    public $expenditure_id;
    public array $monthlyTotals = []; // Ubah ke array
    public $filterMonth = '';
    public $filterYear = '';

    // Array nama bulan dalam bahasa Indonesia
    public array $monthNames = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    public function getMonthNameProperty()
    {
        return isset($this->monthNames[(int) $this->filterMonth])
            ? $this->monthNames[(int) $this->filterMonth]
            : '';
    }

    public function getMaxDateProperty()
    {
        if (!$this->filterMonth || !$this->filterYear) {
            return '';
        }

        try {
            $daysInMonth = Carbon::create($this->filterYear, $this->filterMonth)->daysInMonth;
            return sprintf('%04d-%02d-%02d', $this->filterYear, $this->filterMonth, $daysInMonth);
        } catch (\Exception $e) {
            return '';
        }
    }

    public function mount()
    {
        // Inisialisasi properti dengan nilai default yang aman
        $this->expenditures = [];
        $this->total = 0;
        $this->monthlyTotals = [];

        // TIDAK set filter default - biarkan kosong agar user harus memilih
        $this->filterMonth = '';
        $this->filterYear = '';

        // TIDAK load data awal - akan di-load setelah user memilih filter
    }

    public function loadExpenditures()
    {
        try {
            // Reset data jika filter tidak lengkap
            if (!$this->filterMonth || !$this->filterYear) {
                $this->expenditures = [];
                $this->total = 0;
                $this->monthlyTotals = [];
                return;
            }

            // Validasi filter month dan year
            if (!is_numeric($this->filterMonth) || !is_numeric($this->filterYear)) {
                $this->expenditures = [];
                $this->total = 0;
                $this->monthlyTotals = [];
                return;
            }

            // Load data berdasarkan filter dengan error handling
            $query = Expenditure::query()
                ->whereMonth('tanggal', (int)$this->filterMonth)
                ->whereYear('tanggal', (int)$this->filterYear)
                ->orderBy('tanggal', 'desc');

            // Eksekusi query dan convert ke array untuk Livewire
            $result = $query->get();
            $this->expenditures = $result->toArray();

            // Hitung total pengeluaran
            $this->total = $result->sum('jumlah') ?? 0;

            // Load rekap bulanan
            $this->loadMonthlyTotals();
        } catch (\Exception $e) {
            // Handle error gracefully
            $this->expenditures = [];
            $this->total = 0;
            $this->monthlyTotals = [];

            // Log error untuk debugging
            Log::error('Error in loadExpenditures: ' . $e->getMessage(), [
                'filterMonth' => $this->filterMonth,
                'filterYear' => $this->filterYear,
                'trace' => $e->getTraceAsString()
            ]);

            session()->flash('error', 'Terjadi kesalahan saat memuat data: ' . $e->getMessage());
        }
    }

    public function loadMonthlyTotals()
    {
        try {
            // Menggunakan query database langsung yang lebih efisien
            $monthlyData = Expenditure::selectRaw('
                YEAR(tanggal) as year, 
                MONTH(tanggal) as month, 
                SUM(jumlah) as total
            ')
                ->whereNotNull('tanggal')
                ->groupByRaw('YEAR(tanggal), MONTH(tanggal)')
                ->orderByRaw('YEAR(tanggal) DESC, MONTH(tanggal) DESC')
                ->get();

            // Convert hasil query ke format array dengan key YYYY-MM
            $this->monthlyTotals = $monthlyData->mapWithKeys(function ($item) {
                $key = sprintf('%04d-%02d', $item->year, $item->month);
                return [$key => $item->total];
            })->toArray();
        } catch (\Exception $e) {
            // Fallback ke method Collection jika query raw gagal
            try {
                $expenditures = Expenditure::whereNotNull('tanggal')->get();

                if ($expenditures->isEmpty()) {
                    $this->monthlyTotals = [];
                    return;
                }

                $grouped = $expenditures->groupBy(function ($expenditure) {
                    try {
                        $date = $expenditure->tanggal instanceof \Carbon\Carbon
                            ? $expenditure->tanggal
                            : Carbon::parse($expenditure->tanggal);

                        return $date->format('Y-m');
                    } catch (\Exception $dateException) {
                        return null; // Skip invalid dates
                    }
                });

                // Filter out null keys dan hitung total
                $this->monthlyTotals = $grouped->filter()
                    ->map(function ($monthlyExpenditures) {
                        return $monthlyExpenditures->sum('jumlah');
                    })
                    ->sortKeysDesc()
                    ->toArray();
            } catch (\Exception $fallbackError) {
                $this->monthlyTotals = [];
                Log::error('Error in loadMonthlyTotals fallback: ' . $fallbackError->getMessage());
            }

            Log::error('Error loading monthly totals: ' . $e->getMessage());

            // Hanya tampilkan error message di development
            if (app()->environment('local', 'development')) {
                session()->flash('error', 'Error loading monthly summary: ' . $e->getMessage());
            }
        }
    }

    public function openModal()
    {
        $this->resetInput();

        try {
            // Set tanggal default ke hari ini jika masih dalam bulan/tahun yang dipilih
            $today = now();
            if ($today->month == $this->filterMonth && $today->year == $this->filterYear) {
                $this->tanggal = $today->format('Y-m-d');
            } else {
                // Set ke tanggal pertama bulan yang dipilih
                $this->tanggal = sprintf('%04d-%02d-01', $this->filterYear, $this->filterMonth);
            }
        } catch (\Exception $e) {
            // Fallback jika ada error dengan tanggal
            $this->tanggal = now()->format('Y-m-d');
        }

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->resetInput();
        $this->showModal = false;
    }

    public function save()
    {
        $this->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'required|string|max:255',
            'jumlah' => 'required|numeric|min:0',
        ]);

        try {
            // Validasi tanggal sesuai dengan filter bulan/tahun
            $selectedDate = Carbon::parse($this->tanggal);
            if ($selectedDate->month != $this->filterMonth || $selectedDate->year != $this->filterYear) {
                $this->addError('tanggal', 'Tanggal harus sesuai dengan bulan dan tahun yang dipilih.');
                return;
            }

            $data = [
                'tanggal' => $this->tanggal,
                'keterangan' => trim($this->keterangan),
                'jumlah' => (float) $this->jumlah,
            ];

            if ($this->isEdit && $this->expenditure_id) {
                // Update data
                $expenditure = Expenditure::findOrFail($this->expenditure_id);
                $expenditure->update($data);
                session()->flash('message', 'Data berhasil diperbarui!');
            } else {
                // Create data baru
                Expenditure::create($data);
                session()->flash('message', 'Data berhasil ditambahkan!');
            }

            $this->closeModal();
            $this->loadExpenditures();
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $expenditure = Expenditure::findOrFail($id);

            $this->expenditure_id = $expenditure->id;

            // Handling tanggal yang lebih robust
            if ($expenditure->tanggal instanceof \Carbon\Carbon) {
                $this->tanggal = $expenditure->tanggal->format('Y-m-d');
            } else {
                $this->tanggal = Carbon::parse((string) $expenditure->tanggal)->format('Y-m-d');
            }

            $this->keterangan = $expenditure->keterangan;
            $this->jumlah = $expenditure->jumlah;
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
            $expenditure = Expenditure::findOrFail($id);
            $expenditure->delete();
            $this->loadExpenditures();
            session()->flash('message', 'Data berhasil dihapus!');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    public function clearFilters()
    {
        $this->filterMonth = '';
        $this->filterYear = '';
        $this->expenditures = [];
        $this->total = 0;
        $this->monthlyTotals = [];
        $this->resetInput();
    }

    public function resetInput()
    {
        $this->reset([
            'tanggal',
            'keterangan',
            'jumlah',
            'expenditure_id',
            'isEdit'
        ]);

        // Clear validation errors
        $this->resetValidation();
    }

    // Livewire lifecycle methods untuk reactive filtering
    public function updatedFilterMonth()
    {
        $this->resetInput();
        $this->loadExpenditures();
    }

    public function updatedFilterYear()
    {
        $this->resetInput();
        $this->loadExpenditures();
    }

    // Real-time validation
    public function updatedJumlah()
    {
        $this->validateOnly('jumlah');
    }

    public function updatedKeterangan()
    {
        $this->validateOnly('keterangan');
    }

    public function updatedTanggal()
    {
        $this->validateOnly('tanggal');
    }

    // Helper method untuk mendapatkan expenditures sebagai collection (untuk operasi yang butuh Collection)
    public function getExpendituresCollection()
    {
        return collect($this->expenditures);
    }

    // Method untuk debugging - bisa dihapus nanti
    public function debugExpenditures()
    {
        return [
            'type' => gettype($this->expenditures),
            'count' => count($this->expenditures),
            'is_array' => is_array($this->expenditures),
            'sample' => count($this->expenditures) > 0 ? $this->expenditures[0] : null
        ];
    }

    public function render()
    {
        return view('livewire.expenditures.expenditure-page');
    }
}
