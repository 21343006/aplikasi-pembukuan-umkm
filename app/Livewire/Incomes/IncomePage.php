<?php

namespace App\Livewire\Incomes;

use App\Models\Income;
use Livewire\Component;
use Livewire\Attributes\Title;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class IncomePage extends Component
{
    #[Title('Pendapatan')]
    public $tanggal, $produk, $jumlah_terjual, $harga_satuan;
    public $incomes = [];
    public $jumlah = 0;
    public $showModal = false;
    public $isEdit = false;
    public $income_id;
    public $monthlyTotals = [];
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

    // PERBAIKAN: Hapus method yang typo
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

    public function loadIncomes()
    {
        try {
            // Reset data jika filter tidak lengkap
            if (!$this->filterMonth || !$this->filterYear) {
                $this->incomes = collect([]);
                $this->jumlah = 0;
                $this->monthlyTotals = collect([]);
                return;
            }

            // Load data berdasarkan filter dengan error handling
            $query = Income::query()
                ->whereMonth('tanggal', $this->filterMonth)
                ->whereYear('tanggal', $this->filterYear)
                ->orderBy('tanggal', 'desc');

            $this->incomes = $query->get();

            // PERBAIKAN: Hitung total pendapatan dengan null safety yang benar
            $totalPendapatan = 0;
            foreach ($this->incomes as $income) {
                $jumlahTerjual = (float) ($income->jumlah_terjual ?? 0);
                $hargaSatuan = (float) ($income->harga_satuan ?? 0);
                $totalPendapatan += $jumlahTerjual * $hargaSatuan;
            }
            $this->jumlah = $totalPendapatan;

            // Load rekap bulanan
            $this->loadMonthlyTotals();
        } catch (\Exception $e) {
            // Handle error gracefully
            $this->incomes = collect([]);
            $this->jumlah = 0;
            $this->monthlyTotals = collect([]);
            session()->flash('error', 'Terjadi kesalahan saat memuat data: ' . $e->getMessage());
        }
    }

    public function loadMonthlyTotals()
    {
        try {
            $monthlyData = Income::selectRaw('DATE_FORMAT(tanggal, "%Y-%m") as bulan, SUM(jumlah_terjual * harga_satuan) as total')
                ->groupBy('bulan')
                ->orderBy('bulan', 'desc')
                ->pluck('total', 'bulan');

            $this->monthlyTotals = $monthlyData ?? collect([]);
        } catch (\Exception $e) {
            $this->monthlyTotals = collect([]);
            Log::error('Error loading monthly totals: ' . $e->getMessage());
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
            'produk' => 'required|string|max:255',
            'jumlah_terjual' => 'required|integer|min:1',
            'harga_satuan' => 'required|numeric|min:0',
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
                'produk' => trim($this->produk),
                'jumlah_terjual' => (int) $this->jumlah_terjual,
                'harga_satuan' => (float) $this->harga_satuan,
            ];

            if ($this->isEdit && $this->income_id) {
                // Update data
                $income = Income::findOrFail($this->income_id);
                $income->update($data);
                session()->flash('message', 'Data berhasil diperbarui!');
            } else {
                // Create data baru
                Income::create($data);
                session()->flash('message', 'Data berhasil ditambahkan!');
            }

            $this->closeModal();
            $this->loadIncomes();
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $income = Income::findOrFail($id);

            $this->income_id = $income->id;

            // PERBAIKAN: Handling tanggal yang lebih robust
            if ($income->tanggal instanceof \Carbon\Carbon) {
                $this->tanggal = $income->tanggal->format('Y-m-d');
            } else {
                $this->tanggal = Carbon::parse((string) $income->tanggal)->format('Y-m-d');
            }

            $this->produk = $income->produk;
            $this->jumlah_terjual = $income->jumlah_terjual;
            $this->harga_satuan = $income->harga_satuan;
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
            $income = Income::findOrFail($id);
            $income->delete();
            $this->loadIncomes();
            session()->flash('message', 'Data berhasil dihapus!');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    public function clearFilters()
    {
        $this->filterMonth = '';
        $this->filterYear = '';
        $this->resetInput();
        $this->loadIncomes();
    }

    public function resetInput()
    {
        $this->reset([
            'tanggal',
            'produk',
            'jumlah_terjual',
            'harga_satuan',
            'income_id',
            'isEdit'
        ]);

        // Clear validation errors
        $this->resetValidation();
    }

    // Livewire lifecycle methods untuk reactive filtering
    public function updatedFilterMonth()
    {
        $this->resetInput();
        $this->loadIncomes();
    }

    public function updatedFilterYear()
    {
        $this->resetInput();
        $this->loadIncomes();
    }

    // Real-time calculation untuk preview total
    public function updatedJumlahTerjual()
    {
        // Trigger reactivity untuk preview total di modal
        $this->validateOnly('jumlah_terjual');
    }

    public function updatedHargaSatuan()
    {
        // Trigger reactivity untuk preview total di modal  
        $this->validateOnly('harga_satuan');
    }

    public function updatedProduk()
    {
        $this->validateOnly('produk');
    }

    public function updatedTanggal()
    {
        $this->validateOnly('tanggal');
    }

    public function render()
    {
        return view('livewire.incomes.income-page');
    }
}
