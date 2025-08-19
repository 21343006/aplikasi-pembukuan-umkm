<?php

namespace App\Livewire\Beps;

use App\Models\Bep;
use App\Models\Expenditure;
use App\Models\FixedCost;
use App\Models\Income;
use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BepForm extends Component
{
    #[Title('Analisis Titik Impas (BEP)')]

    public $mode = 'perProduct'; // 'perProduct' or 'perPeriod'

    // Properti untuk mode "Per Produk"
    public $beploadbep = [];
    public $showModal = false;
    public $isEdit = false;
    public $bep_id;
    public $selectedProduk = '';
    public $avgSellingPrice = 0;
    public $modal_per_barang = 0;
    public $produkList = [];

    // Properti untuk mode "Per Periode"
    public $selectedYear;
    public $selectedMonth;
    public $totalFixedCost = 0;
    public $totalSales = 0;
    public $totalVariableCost = 0;
    public $contributionMarginRatio = 0;
    public $bepRupiahPeriod = 0;
    public $calculationError = null;
    public $periodDataLoaded = false;

    protected function rules()
    {
        if ($this->mode === 'perProduct') {
            return [
                'selectedProduk' => 'required|string',
                'totalFixedCost' => 'required|numeric|min:0',
                'avgSellingPrice' => 'required|numeric|gt:0',
                'modal_per_barang' => 'required|numeric|min:0|lt:avgSellingPrice',
            ];
        }
        return [];
    }

    protected $messages = [
        'selectedProduk.required' => 'Silakan pilih produk terlebih dahulu.',
        'totalFixedCost.min' => 'Total biaya tetap harus angka positif.',
        'avgSellingPrice.gt' => 'Harga jual rata-rata tidak dapat ditemukan atau nol.',
        'modal_per_barang.required' => 'Biaya variabel per unit wajib diisi.',
        'modal_per_barang.min' => 'Biaya variabel tidak boleh kurang dari 0.',
        'modal_per_barang.lt' => 'Biaya variabel harus lebih rendah dari harga jual rata-rata.',
    ];

    public function mount()
    {
        $this->selectedYear = now()->year;
        $this->selectedMonth = now()->month;
        $this->loadDataForMode();
    }

    public function switchMode($mode)
    {
        $this->mode = $mode;
        $this->loadDataForMode();
    }

    public function loadDataForMode()
    {
        if ($this->mode === 'perProduct') {
            $this->loadPerProductData();
        } else {
            // Jangan langsung hitung, tunggu user klik tombol
            $this->periodDataLoaded = false; 
        }
    }

    // ============================================
    // LOGIKA UNTUK MODE "PER PRODUK"
    // ============================================

    public function loadPerProductData()
    {
        if (!Auth::check()) return;
        try {
            // Untuk BEP per produk, biaya tetap tetap dihitung total sebagai asumsi dasar
            $this->totalFixedCost = (float) FixedCost::where('user_id', Auth::id())->sum('nominal');
            $this->produkList = Income::where('user_id', Auth::id())
                ->whereNotNull('produk')
                ->where('produk', '!=', '')
                ->distinct()
                ->orderBy('produk')
                ->pluck('produk')
                ->toArray();
            $this->beploadbep = Bep::where('user_id', Auth::id())->latest()->get();
        } catch (\Exception $e) {
            Log::error('Error loading per-product BEP data: ' . $e->getMessage());
            session()->flash('error', 'Gagal memuat data awal untuk perhitungan BEP.');
        }
    }

    public function updatedSelectedProduk($produk)
    {
        if (empty($produk)) {
            $this->avgSellingPrice = 0;
            return;
        }
        try {
            $this->avgSellingPrice = (float) Income::where('user_id', Auth::id())
                ->where('produk', $produk)
                ->avg('harga_satuan');
        } catch (\Exception $e) {
            $this->avgSellingPrice = 0;
        }
    }

    public function save()
    {
        if (!Auth::check()) return;
        $this->validate();
        try {
            $data = [
                'user_id' => Auth::id(),
                'nama_produk' => $this->selectedProduk,
                'modal_tetap' => $this->totalFixedCost,
                'harga_per_barang' => $this->avgSellingPrice,
                'modal_per_barang' => (float) $this->modal_per_barang,
            ];
            if ($this->isEdit && $this->bep_id) {
                Bep::where('user_id', Auth::id())->findOrFail($this->bep_id)->update($data);
                session()->flash('message', 'Data BEP berhasil diperbarui.');
            } else {
                Bep::create($data);
                session()->flash('message', 'Perhitungan BEP berhasil disimpan.');
            }
            $this->closeModal();
            $this->loadPerProductData();
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan saat menyimpan data BEP.');
        }
    }

    public function edit($id) { /* ... (logika edit tidak berubah) ... */ }
    public function delete($id) { /* ... (logika delete tidak berubah) ... */ }
    public function openModal() { $this->resetInput(); $this->loadPerProductData(); $this->showModal = true; }
    public function closeModal() { $this->resetInput(); $this->showModal = false; }
    public function resetInput() { /* ... (logika reset tidak berubah) ... */ }
    public function getContributionMarginProperty() { return (float)$this->avgSellingPrice - (float)$this->modal_per_barang; }
    public function getBepUnitProperty() { return ($this->contributionMargin > 0 && $this->totalFixedCost > 0) ? ceil((float)$this->totalFixedCost / $this->contributionMargin) : 0; }
    public function getBepRupiahProperty() { return $this->bepUnit > 0 ? $this->bepUnit * (float)$this->avgSellingPrice : 0; }

    // ============================================
    // LOGIKA UNTUK MODE "PER PERIODE"
    // ============================================

    public function calculatePeriodBep()
    {
        if (!Auth::check() || !$this->selectedMonth || !$this->selectedYear) {
            $this->calculationError = "Silakan pilih bulan dan tahun terlebih dahulu.";
            return;
        }

        try {
            $this->resetPeriodCalculation();
            $year = (int)$this->selectedYear;
            $month = (int)$this->selectedMonth;

            // 1. Biaya Tetap untuk periode yang dipilih
            $this->totalFixedCost = (float) FixedCost::where('user_id', Auth::id())
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->sum('nominal');

            // 2. Total Penjualan untuk periode yang dipilih
            $this->totalSales = (float) Income::where('user_id', Auth::id())
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->get()
                ->sum('total'); // Menggunakan accessor 'total'

            // 3. Biaya Variabel (dari pengeluaran) untuk periode yang dipilih
            $this->totalVariableCost = (float) Expenditure::where('user_id', Auth::id())
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->sum('jumlah');

            if ($this->totalSales <= 0) {
                $this->calculationError = 'Tidak ada data penjualan untuk periode ini. BEP tidak dapat dihitung.';
                return;
            }
            if ($this->totalSales <= $this->totalVariableCost) {
                $this->calculationError = 'Total biaya variabel melebihi total penjualan. BEP tidak dapat tercapai.';
                return;
            }

            $contributionMargin = $this->totalSales - $this->totalVariableCost;
            $this->contributionMarginRatio = ($contributionMargin / $this->totalSales) * 100;

            if ($this->contributionMarginRatio <= 0) {
                $this->calculationError = 'Rasio margin kontribusi nol atau negatif. BEP tidak dapat tercapai.';
                return;
            }
            
            $this->bepRupiahPeriod = $this->totalFixedCost / ($this->contributionMarginRatio / 100);
            $this->periodDataLoaded = true;

        } catch (\Exception $e) {
            Log::error('Error calculating periodic BEP: ' . $e->getMessage());
            $this->calculationError = 'Terjadi kesalahan saat melakukan perhitungan.';
        }
    }

    private function resetPeriodCalculation()
    {
        $this->totalFixedCost = 0;
        $this->totalSales = 0;
        $this->totalVariableCost = 0;
        $this->contributionMarginRatio = 0;
        $this->bepRupiahPeriod = 0;
        $this->calculationError = null;
        $this->periodDataLoaded = false;
    }
    
    public function getAvailableYearsProperty()
    {
        return range(now()->year, 2020);
    }

    public function getMonthNamesProperty()
    {
        return [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
    }

    public function render()
    {
        return view('livewire.beps.bep-form');
    }
}
