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

    public $mode = 'perPeriod'; // 'perPeriod' (bulanan) atau 'perDay' (harian)

    // Properti untuk mode "Per Produk"
    public $beploadbep = [];
    public $showModal = false;
    public $isEdit = false;
    public $bep_id;
    public $selectedProduk = '';
    public $avgSellingPrice = 0;
    public $modal_per_barang = 0;
    public $produkList = [];

    // Kalkulator BEP Otomatis (berbasis periode terpilih)
    public $calcSelectedProduk = '';
    public $calcSellingPrice = 0;
    public $calcVariableCost = 0;
    public $calcFixedCost = 0;
    public $calcUnitsSold = 0;

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

    // Derived metrics for per-product mode
    public $unitsSoldCache = [];

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
        $this->recomputeCalcVariableCost();
        $this->recomputeCalcFixedCost();
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
            // Pastikan daftar produk tersedia untuk kalkulator
            $this->produkList = $this->fetchProdukList();
            $this->recomputeCalcVariableCost();
            $this->recomputeCalcFixedCost();
        }
    }

    // ============================================
    // LOGIKA UNTUK MODE "PER PRODUK"
    // ============================================

    public function loadPerProductData()
    {
        if (!Auth::check()) return;
        try {
            // Untuk BEP per produk, biaya tetap dihitung per bulan
            $this->totalFixedCost = (float) FixedCost::where('user_id', Auth::id())
                ->whereYear('tanggal', $this->selectedYear)
                ->whereMonth('tanggal', $this->selectedMonth)
                ->sum('nominal');
            $this->produkList = $this->fetchProdukList();
            $this->beploadbep = Bep::where('user_id', Auth::id())->latest()->get();
        } catch (\Exception $e) {
            Log::error('Error loading per-product BEP data: ' . $e->getMessage());
            session()->flash('error', 'Gagal memuat data awal untuk perhitungan BEP.');
        }
    }

    private function fetchProdukList()
    {
        try {
            return Income::where('user_id', Auth::id())
                ->whereNotNull('produk')
                ->where('produk', '!=', '')
                ->distinct()
                ->orderBy('produk')
                ->pluck('produk')
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    public function updatedSelectedProduk($produk)
    {
        if (empty($produk)) {
            $this->avgSellingPrice = 0;
            return;
        }
        try {
            // Try to use average price in the selected period first
            $avgInPeriod = Income::where('user_id', Auth::id())
                ->where('produk', $produk)
                ->when($this->selectedYear, fn($q) => $q->whereYear('tanggal', $this->selectedYear))
                ->when($this->selectedMonth, fn($q) => $q->whereMonth('tanggal', $this->selectedMonth))
                ->avg('harga_satuan');

            if ($avgInPeriod && $avgInPeriod > 0) {
                $this->avgSellingPrice = (float) $avgInPeriod;
            } else {
                // Fallback to overall average price for the product
                $this->avgSellingPrice = (float) Income::where('user_id', Auth::id())
                    ->where('produk', $produk)
                    ->avg('harga_satuan');
            }
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
            // Gunakan perhitungan eksplisit agar konsisten: jumlah_terjual * harga_satuan
            $this->totalSales = (float) Income::where('user_id', Auth::id())
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->get()
                ->sum(function ($income) {
                    return ((float) ($income->jumlah_terjual ?? 0)) * ((float) ($income->harga_satuan ?? 0));
                });

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

    // ============================
    // Helpers and reactive updates
    // ============================

    public function updatedSelectedMonth()
    {
        // Reload dependent data for per-product mode when period changes
        if ($this->mode === 'perProduct') {
            $this->loadPerProductData();
            // Clear units sold cache because period changed
            $this->unitsSoldCache = [];
        } else {
            // Reset period calculations for per-period mode when filters change
            $this->resetPeriodCalculation();
            $this->refreshCalcFromProduk();
            $this->recomputeCalcVariableCost();
            $this->recomputeCalcFixedCost();
        }
    }

    public function updatedSelectedYear()
    {
        // Sanitize manual input
        $this->selectedYear = trim((string) $this->selectedYear);
        if (!is_numeric($this->selectedYear)) {
            $this->selectedYear = now()->year;
        } else {
            $y = (int) $this->selectedYear;
            if ($y < 2000 || $y > 2099) {
                $y = (int) now()->year;
            }
            $this->selectedYear = $y;
        }

        // Reload dependent data for per-product mode when period changes
        if ($this->mode === 'perProduct') {
            $this->loadPerProductData();
            $this->unitsSoldCache = [];
        } else {
            $this->resetPeriodCalculation();
            $this->refreshCalcFromProduk();
            $this->recomputeCalcVariableCost();
            $this->recomputeCalcFixedCost();
        }
    }

    public function updatedCalcSelectedProduk($produk)
    {
        if (empty($produk)) {
            $this->calcSellingPrice = 0;
            $this->calcUnitsSold = 0;
            return;
        }
        try {
            // Harga jual rata-rata di periode terpilih (fallback keseluruhan)
            $avgInPeriod = Income::where('user_id', Auth::id())
                ->where('produk', $produk)
                ->when($this->selectedYear, fn($q) => $q->whereYear('tanggal', $this->selectedYear))
                ->when($this->selectedMonth, fn($q) => $q->whereMonth('tanggal', $this->selectedMonth))
                ->avg('harga_satuan');

            $this->calcSellingPrice = $avgInPeriod && $avgInPeriod > 0
                ? (float) $avgInPeriod
                : (float) Income::where('user_id', Auth::id())
                    ->where('produk', $produk)
                    ->avg('harga_satuan');

            // Units terjual untuk periode terpilih
            $this->calcUnitsSold = (int) Income::where('user_id', Auth::id())
                ->where('produk', $produk)
                ->when($this->selectedYear, fn($q) => $q->whereYear('tanggal', $this->selectedYear))
                ->when($this->selectedMonth, fn($q) => $q->whereMonth('tanggal', $this->selectedMonth))
                ->sum('jumlah_terjual');
        } catch (\Exception $e) {
            $this->calcSellingPrice = 0;
            $this->calcUnitsSold = 0;
        }
    }

    private function refreshCalcFromProduk()
    {
        if (!empty($this->calcSelectedProduk)) {
            $this->updatedCalcSelectedProduk($this->calcSelectedProduk);
        }
    }

    public function getCalcMonthlySalesProperty()
    {
        $price = (float) $this->calcSellingPrice;
        $units = (int) $this->calcUnitsSold;
        return $price > 0 && $units > 0 ? $price * $units : 0.0;
    }

    public function getCalcContributionMarginTotalProperty()
    {
        // Total margin kontribusi bulan ini (approx) = Penjualan Bulanan - Biaya Variabel (Bulan)
        $sales = (float) $this->calcMonthlySales;
        $variableMonthly = (float) $this->calcVariableCost;
        return max(0.0, $sales - $variableMonthly);
    }

    public function getCalcContributionMarginRatioProperty()
    {
        $sales = (float) $this->calcMonthlySales;
        if ($sales <= 0) {
            return 0.0;
        }
        $cmTotal = (float) $this->calcContributionMarginTotal;
        return max(0.0, ($cmTotal / $sales) * 100.0);
    }

    public function getCalcBepUnitsProperty()
    {
        $price = (float) $this->calcSellingPrice;
        $bepRupiah = (float) $this->calcBepRupiah;
        if ($price <= 0 || $bepRupiah <= 0) {
            return 0;
        }
        return (int) ceil($bepRupiah / $price);
    }

    public function getCalcBepRupiahProperty()
    {
        // BEP (Rp) = Fixed Cost / CMR
        $fixed = (float) $this->calcFixedCost;
        $cmr = (float) $this->calcContributionMarginRatio; // percent
        if ($fixed <= 0 || $cmr <= 0) {
            return 0.0;
        }
        return $fixed / ($cmr / 100.0);
    }

    public function getCalcUnitsRemainingProperty()
    {
        $remaining = (int) $this->calcBepUnits - (int) $this->calcUnitsSold;
        return $remaining > 0 ? $remaining : 0;
    }

    /**
     * Get total units sold for a specific product in the currently selected period.
     */
    public function getUnitsSoldForProduct($product)
    {
        try {
            if (!Auth::check() || empty($product)) {
                return 0;
            }

            $cacheKey = ($this->selectedYear ?: 'y') . '-' . ($this->selectedMonth ?: 'm') . '-' . $product;
            if (array_key_exists($cacheKey, $this->unitsSoldCache)) {
                return (int) $this->unitsSoldCache[$cacheKey];
            }

            $query = Income::where('user_id', Auth::id())
                ->where('produk', $product);

            if (!empty($this->selectedYear)) {
                $query->whereYear('tanggal', (int) $this->selectedYear);
            }
            if (!empty($this->selectedMonth)) {
                $query->whereMonth('tanggal', (int) $this->selectedMonth);
            }

            $units = (int) ($query->sum('jumlah_terjual') ?? 0);
            $this->unitsSoldCache[$cacheKey] = $units;
            return $units;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Units sold for the currently selected product in the selected period.
     */
    public function getUnitsSoldInPeriodProperty()
    {
        if (empty($this->selectedProduk)) {
            return 0;
        }
        return $this->getUnitsSoldForProduct($this->selectedProduk);
    }

    /**
     * Remaining units to reach BEP for the currently selected product.
     */
    public function getUnitsRemainingProperty()
    {
        $remaining = (int) $this->bepUnit - (int) $this->unitsSoldInPeriod;
        return $remaining > 0 ? $remaining : 0;
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

    private function recomputeCalcVariableCost(): void
    {
        try {
            if (!Auth::check() || empty($this->selectedYear) || empty($this->selectedMonth)) {
                $this->calcVariableCost = 0;
                return;
            }
            $this->calcVariableCost = (float) Expenditure::where('user_id', Auth::id())
                ->whereYear('tanggal', (int)$this->selectedYear)
                ->whereMonth('tanggal', (int)$this->selectedMonth)
                ->sum('jumlah');
        } catch (\Exception $e) {
            $this->calcVariableCost = 0;
        }
    }

    private function recomputeCalcFixedCost(): void
    {
        try {
            if (!Auth::check() || empty($this->selectedYear) || empty($this->selectedMonth)) {
                $this->calcFixedCost = 0;
                return;
            }
            $this->calcFixedCost = (float) FixedCost::where('user_id', Auth::id())
                ->whereYear('tanggal', (int)$this->selectedYear)
                ->whereMonth('tanggal', (int)$this->selectedMonth)
                ->sum('nominal');
        } catch (\Exception $e) {
            $this->calcFixedCost = 0;
        }
    }

    public function render()
    {
        return view('livewire.beps.bep-form');
    }
}
