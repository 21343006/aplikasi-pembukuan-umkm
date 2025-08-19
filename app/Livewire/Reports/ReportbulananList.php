<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Reportharian;
use App\Models\Capitalearly;
use App\Models\Capital;
use App\Models\FixedCost;
use App\Models\Income;
use App\Models\Expenditure;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class ReportbulananList extends Component
{
    public $bulan;
    public $tahun;
    public $rekapHarian = [];
    
    // Saldo calculations
    public $saldoAwal = 0;
    public $saldoAkhirBulanIni = 0;
    public $totalSaldoKumulatif = 0;
    
    // Main transaction totals
    public $totalUangMasuk = 0;
    public $totalUangKeluar = 0;
    
    // Modal calculations
    public $totalModalAwal = 0;
    public $totalModalKeluar = 0;
    public $totalModalTetap = 0;
    
    // Combined totals
    public $totalPemasukanBulanIni = 0;
    public $totalPengeluaranBulanIni = 0;
    public $monthlySummaryKeterangan;

    /**
     * Initialize component with current month and year
     */
    public function mount(): void
    {
        $this->bulan = now()->month;
        $this->tahun = now()->year;
        $this->loadRekap();
    }

    /**
     * Handle month change
     */
    public function updatedBulan(): void
    {
        $this->loadRekap();
    }

    /**
     * Handle year change
     */
    public function updatedTahun(): void
    {
        $this->loadRekap();
    }

    /**
     * Load monthly recap data with all calculations
     */
    public function loadRekap(): void
    {
        $this->resetAllTotals();

        $bulan = (int) $this->bulan;
        $tahun = (int) $this->tahun;

        if ($bulan < 1 || $bulan > 12 || $tahun < 2020) {
            return;
        }

        try {
            // Calculate cumulative balance from all data before this month
            $this->saldoAwal = $this->hitungSaldoSebelumBulan($bulan, $tahun);

            // Dynamically load daily income from Income model
            $incomeDaily = Income::whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', $bulan)
                ->selectRaw('tanggal, SUM(jumlah_terjual * harga_satuan) as total_masuk')
                ->groupBy('tanggal')
                ->get()
                ->keyBy(function($item) {
                    return Carbon::parse($item->tanggal)->format('Y-m-d');
                });

            // Dynamically load daily expenditure from Expenditure model
            $expenditureDaily = Expenditure::whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', $bulan)
                ->selectRaw('tanggal, SUM(jumlah) as total_keluar')
                ->groupBy('tanggal')
                ->get()
                ->keyBy(function($item) {
                    return Carbon::parse($item->tanggal)->format('Y-m-d');
                });

            // Create a collection for daily income/expenditure
            $dailyTransactionsCollection = collect();
            $daysInMonth = Carbon::create($tahun, $bulan, 1)->daysInMonth;

            for ($i = 1; $i <= $daysInMonth; $i++) {
                $currentDate = Carbon::create($tahun, $bulan, $i)->format('Y-m-d');
                $masuk = $incomeDaily->get($currentDate)->total_masuk ?? 0;
                $keluar = $expenditureDaily->get($currentDate)->total_keluar ?? 0;

                $dailyTransactionsCollection->push((object)[
                    'tanggal' => $currentDate,
                    'masuk' => (float) $masuk,
                    'keluar' => (float) $keluar,
                ]);
            }

            // Load modal awal data for this month
            $modalAwalData = Capitalearly::whereYear('tanggal_input', $tahun)
                ->whereMonth('tanggal_input', $bulan)
                ->orderBy('tanggal_input')
                ->get();

            $modalCollection = $modalAwalData->map(function ($item) {
                return (object)[
                    'tanggal' => Carbon::parse($item->tanggal_input)->format('Y-m-d'),
                    'masuk' => (float) $item->modal_awal,
                    'keluar' => 0,
                ];
            });

            // Load modal tetap (fixed costs) for this month - add to 1st day as expense
            $modalTetapData = FixedCost::whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', $bulan)
                ->sum('nominal') ?? 0;

            $this->totalModalTetap = $modalTetapData;

            // Create fixed cost entry for the 1st day of the month
            $firstDayOfMonth = Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
            $fixedCostCollection = collect();
            
            if ($modalTetapData > 0) {
                $fixedCostCollection->push((object)[
                    'tanggal' => $firstDayOfMonth,
                    'masuk' => 0,
                    'keluar' => (float) $modalTetapData,
                ]);
            }

            // Load modal keluar data for this month (if column exists)
            $modalKeluarCollection = collect();
            $this->totalModalKeluar = 0;
            
            if ($this->hasColumn('capitals', 'jenis')) {
                $modalKeluarData = Capital::where('jenis', 'keluar')
                    ->whereYear('tanggal', $tahun)
                    ->whereMonth('tanggal', $bulan)
                    ->orderBy('tanggal')
                    ->get();

                $this->totalModalKeluar = $modalKeluarData->sum('nominal') ?? 0;

                $modalKeluarCollection = $modalKeluarData->map(function ($item) {
                    return (object)[
                        'tanggal' => Carbon::parse($item->tanggal)->format('Y-m-d'),
                        'masuk' => 0,
                        'keluar' => (float) $item->nominal,
                    ];
                });
            }

            // Merge all collections, now including dynamic daily transactions
            $merged = $dailyTransactionsCollection
                ->concat($modalCollection)
                ->concat($fixedCostCollection)
                ->concat($modalKeluarCollection);

            $grouped = $merged->groupBy('tanggal')->map(function ($items) {
                return [
                    'masuk' => $items->sum('masuk'),
                    'keluar' => $items->sum('keluar'),
                ];
            });

            $this->rekapHarian = $grouped->sortKeys()->toArray();

            // Calculate main transaction totals dynamically from Income and Expenditure
            $this->totalUangMasuk = Income::whereYear('tanggal', $tahun)
                                        ->whereMonth('tanggal', $bulan)
                                        ->selectRaw('SUM(jumlah_terjual * harga_satuan) as total_income')
                                        ->value('total_income') ?? 0;

            $this->totalUangKeluar = Expenditure::whereYear('tanggal', $tahun)
                                            ->whereMonth('tanggal', $bulan)
                                            ->sum('jumlah') ?? 0;

            // Calculate modal awal total for this month
            $this->totalModalAwal = $modalAwalData->sum('modal_awal');

            // Calculate combined totals
            $this->totalPemasukanBulanIni = $this->totalUangMasuk + $this->totalModalAwal;
            $this->totalPengeluaranBulanIni = $this->totalUangKeluar + $this->totalModalKeluar + $this->totalModalTetap;

            // Calculate final balance
            $this->saldoAkhirBulanIni = $this->totalPemasukanBulanIni - $this->totalPengeluaranBulanIni;
            $this->totalSaldoKumulatif = $this->saldoAwal + $this->saldoAkhirBulanIni;

            // Populate monthly summary keterangan
            $this->monthlySummaryKeterangan = "Ringkasan Bulanan - Pemasukan: " . $this->formatCurrency($this->totalPemasukanBulanIni) . ", Pengeluaran: " . $this->formatCurrency($this->totalPengeluaranBulanIni);

        } catch (\Exception $e) {
            Log::error('Gagal memuat rekap bulanan: ' . $e->getMessage(), [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->resetAllTotals();
        }
    }

    /**
     * Calculate total cumulative balance from all periods before selected month
     * Enhanced to include modal awal, modal keluar, and fixed costs
     */
    private function hitungSaldoSebelumBulan(int $bulan, int $tahun): float
    {
        try {
            $totalSaldo = 0;
            $targetDate = Carbon::create($tahun, $bulan, 1);

            // Calculate from all Income before this month
            $totalIncomeBefore = Income::where('tanggal', '<', $targetDate)
                                    ->selectRaw('SUM(jumlah_terjual * harga_satuan) as total_income')
                                    ->value('total_income') ?? 0;

            // Calculate from all Expenditure before this month
            $totalExpenditureBefore = Expenditure::where('tanggal', '<', $targetDate)
                                            ->sum('jumlah') ?? 0;

            $totalSaldo += $totalIncomeBefore - $totalExpenditureBefore;

            // Calculate from all initial capital before this month  
            $allModal = Capitalearly::where('tanggal_input', '<', $targetDate)->get();
            
            // Total from initial capital
            $totalSaldo += $allModal->sum('modal_awal');

            // Calculate from capital outflow before this month (if column exists)
            if ($this->hasColumn('capitals', 'jenis')) {
                $totalModalKeluar = Capital::where('jenis', 'keluar')
                    ->where('tanggal', '<', $targetDate)
                    ->sum('nominal') ?? 0;

                $totalSaldo -= $totalModalKeluar;
            }

            // Calculate from fixed costs before this month
            $totalFixedCosts = FixedCost::where('tanggal', '<', $targetDate)
                ->sum('nominal') ?? 0;

            $totalSaldo -= $totalFixedCosts;

            return $totalSaldo;

        } catch (\Exception $e) {
            Log::error('Error menghitung saldo sebelum bulan: ' . $e->getMessage(), [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            return 0;
        }
    }

    /**
     * Reset all totals to zero
     */
    private function resetAllTotals(): void
    {
        $this->rekapHarian = [];
        $this->saldoAwal = 0;
        $this->saldoAkhirBulanIni = 0;
        $this->totalSaldoKumulatif = 0;
        $this->totalUangMasuk = 0;
        $this->totalUangKeluar = 0;
        $this->totalModalAwal = 0;
        $this->totalModalKeluar = 0;
        $this->totalModalTetap = 0;
        $this->totalPemasukanBulanIni = 0;
        $this->totalPengeluaranBulanIni = 0;
    }

    /**
     * Check if a column exists in a table
     */
    private function hasColumn(string $table, string $column): bool
    {
        try {
            return Schema::hasColumn($table, $column);
        } catch (\Exception $e) {
            Log::warning("Error checking column {$column} in table {$table}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get detailed breakdown of monthly data
     */
    public function getDetailedBreakdown(): array
    {
        return [
            'periode' => [
                'bulan' => $this->bulan,
                'tahun' => $this->tahun,
                'nama_bulan' => $this->getNamaBulan((int) $this->bulan)
            ],
            'saldo' => [
                'awal' => $this->saldoAwal,
                'perubahan_bulan_ini' => $this->saldoAkhirBulanIni,
                'kumulatif' => $this->totalSaldoKumulatif
            ],
            'pemasukan' => [
                'transaksi_harian' => $this->totalUangMasuk,
                'modal_awal' => $this->totalModalAwal,
                'total' => $this->totalPemasukanBulanIni
            ],
            'pengeluaran' => [
                'transaksi_harian' => $this->totalUangKeluar,
                'modal_keluar' => $this->totalModalKeluar,
                'modal_tetap' => $this->totalModalTetap,
                'total' => $this->totalPengeluaranBulanIni
            ]
        ];
    }

    /**
     * Get month name in Indonesian
     */
    private function getNamaBulan(int $bulan): string
    {
        $namaButel = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return $namaButel[$bulan] ?? 'Unknown';
    }

    /**
     * Get available years for filter
     */
    public function getAvailableYears(): array
    {
        $currentYear = now()->year;
        return range($currentYear + 1, 2020);
    }

    /**
     * Get available months
     */
    public function getAvailableMonths(): array
    {
        return [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
    }

    /**
     * Format currency for display
     */
    public function formatCurrency(float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    /**
     * Get percentage of expenses vs income
     */
    public function getExpensePercentage(): float
    {
        if ($this->totalPemasukanBulanIni <= 0) {
            return 0;
        }

        return round(($this->totalPengeluaranBulanIni / $this->totalPemasukanBulanIni) * 100, 2);
    }

    /**
     * Check if current month has any data
     */
    public function hasDataForCurrentMonth(): bool
    {
        return $this->totalPemasukanBulanIni > 0 || $this->totalPengeluaranBulanIni > 0;
    }

    /**
     * Get monthly performance indicator
     */
    public function getMonthlyPerformance(): string
    {
        if ($this->saldoAkhirBulanIni > 0) {
            return 'surplus';
        } elseif ($this->saldoAkhirBulanIni < 0) {
            return 'deficit';
        } else {
            return 'balanced';
        }
    }

    /**
     * Get detailed modal breakdown for this month
     */
    public function getModalBreakdown(): array
    {
        $bulan = (int) $this->bulan;
        $tahun = (int) $this->tahun;

        try {
            // Get modal awal details
            $modalAwalDetails = Capitalearly::whereYear('tanggal_input', $tahun)
                ->whereMonth('tanggal_input', $bulan)
                ->select('tanggal_input', 'modal_awal')
                ->orderBy('tanggal_input')
                ->get()
                ->toArray();

            // Get modal keluar details (if column exists)
            $modalKeluarDetails = [];
            if ($this->hasColumn('capitals', 'jenis')) {
                $modalKeluarDetails = Capital::where('jenis', 'keluar')
                    ->whereYear('tanggal', $tahun)
                    ->whereMonth('tanggal', $bulan)
                    ->select('tanggal', 'nominal', 'keperluan', 'keterangan')
                    ->orderBy('tanggal')
                    ->get()
                    ->toArray();
            }

            // Get fixed cost details
            $modalTetapDetails = FixedCost::whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', $bulan)
                ->select('tanggal', 'keperluan', 'nominal')
                ->orderBy('keperluan')
                ->get()
                ->toArray();

            return [
                'modal_awal' => $modalAwalDetails,
                'modal_keluar' => $modalKeluarDetails,
                'modal_tetap' => $modalTetapDetails
            ];

        } catch (\Exception $e) {
            Log::error('Error getting modal breakdown: ' . $e->getMessage(), [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'user_id' => Auth::id()
            ]);
            return [
                'modal_awal' => [],
                'modal_keluar' => [],
                'modal_tetap' => []
            ];
        }
    }

    /**
     * Export monthly data to array for external use
     */
    public function exportMonthlyData(): array
    {
        return [
            'periode' => [
                'bulan' => $this->bulan,
                'tahun' => $this->tahun,
                'nama_bulan' => $this->getNamaBulan((int) $this->bulan)
            ],
            'saldo' => [
                'awal' => $this->saldoAwal,
                'akhir_bulan_ini' => $this->saldoAkhirBulanIni,
                'kumulatif' => $this->totalSaldoKumulatif
            ],
            'transaksi_harian' => [
                'masuk' => $this->totalUangMasuk,
                'keluar' => $this->totalUangKeluar,
                'net' => $this->totalUangMasuk - $this->totalUangKeluar
            ],
            'modal' => [
                'awal' => $this->totalModalAwal,
                'keluar' => $this->totalModalKeluar,
                'tetap' => $this->totalModalTetap
            ],
            'total' => [
                'pemasukan' => $this->totalPemasukanBulanIni,
                'pengeluaran' => $this->totalPengeluaranBulanIni,
                'net' => $this->saldoAkhirBulanIni
            ],
            'rekap_harian' => $this->rekapHarian,
            'performance' => $this->getMonthlyPerformance(),
            'expense_percentage' => $this->getExpensePercentage()
        ];
    }

    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.reports.reportbulanan-list', [
            'availableYears' => $this->getAvailableYears(),
            'availableMonths' => $this->getAvailableMonths(),
            'detailedBreakdown' => $this->getDetailedBreakdown(),
            'modalBreakdown' => $this->getModalBreakdown(),
            'monthlyPerformance' => $this->getMonthlyPerformance(),
            'expensePercentage' => $this->getExpensePercentage(),
            'hasData' => $this->hasDataForCurrentMonth()
        ]);
    }
}