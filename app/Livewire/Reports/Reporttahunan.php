<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Income;
use App\Models\Expenditure;
use App\Models\FixedCost;
use App\Models\Debt;
use App\Models\Receivable;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Reporttahunan extends Component
{
    public $tahun;
    public $laporan = [];
    public $yearlySummary = [];
    public $comparisonData = [];
    public $stats = [];
    public $chartData = [];
    
    // Data Utang & Piutang
    public $debtReceivableData = [];
    public $currentDebtReceivableStatus = [];

    public function mount()
    {
        $this->tahun = now()->year; // Default: tahun ini
        $this->generateReport();
    }

    public function updatedTahun()
    {
        $this->generateReport();
    }

    public function refreshReport()
    {
        $this->generateReport();
    }

    public function generateReport()
    {
        $this->laporan = [];
        $monthlyData = [];

        // 1. Generate monthly report
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $total_income = Income::whereYear('tanggal', $this->tahun)
                ->whereMonth('tanggal', $bulan)
                ->sum(DB::raw('jumlah_terjual * harga_satuan'));

            // Pengeluaran dari Expenditure (biaya variabel)
            $total_expenditure = Expenditure::whereYear('tanggal', $this->tahun)
                ->whereMonth('tanggal', $bulan)
                ->sum('jumlah');

            // Tambahkan biaya tetap (FixedCost) ke pengeluaran
            $total_fixed_cost = FixedCost::whereYear('tanggal', $this->tahun)
                ->whereMonth('tanggal', $bulan)
                ->sum('nominal');

            // Total pengeluaran = biaya variabel + biaya tetap
            $total_pengeluaran = $total_expenditure + $total_fixed_cost;

            $laba = $total_income - $total_pengeluaran;

            // Hitung utang dibayar dan piutang diterima per bulan
            // Gunakan paid_date untuk tanggal pembayaran yang lebih akurat
            $utang_dibayar = Debt::whereYear('paid_date', $this->tahun)
                ->whereMonth('paid_date', $bulan)
                ->where('paid_amount', '>', 0)
                ->sum('paid_amount');
                
            $piutang_diterima = Receivable::whereYear('paid_date', $this->tahun)
                ->whereMonth('paid_date', $bulan)
                ->where('paid_amount', '>', 0)
                ->sum('paid_amount');

            $monthlyData[$bulan] = [
                'bulan' => Carbon::create()->month($bulan)->translatedFormat('F'),
                'pendapatan' => $total_income,
                'pengeluaran_variabel' => $total_expenditure,
                'pengeluaran_tetap' => $total_fixed_cost,
                'pengeluaran' => $total_pengeluaran,
                'laba' => $laba,
                'utang_dibayar' => $utang_dibayar,
                'piutang_diterima' => $piutang_diterima,
            ];
        }
        $this->laporan = $monthlyData;

        // 2. Yearly Summary
        $totalPendapatan = array_sum(array_column($this->laporan, 'pendapatan'));
        $totalPengeluaranVariabel = array_sum(array_column($this->laporan, 'pengeluaran_variabel'));
        $totalPengeluaranTetap = array_sum(array_column($this->laporan, 'pengeluaran_tetap'));
        $totalPengeluaran = array_sum(array_column($this->laporan, 'pengeluaran'));
        $totalLaba = $totalPendapatan - $totalPengeluaran;

        // Hitung utang dibayar dan piutang diterima
        // Gunakan paid_date untuk tanggal pembayaran yang lebih akurat
        $totalUtangDibayar = Debt::whereYear('paid_date', $this->tahun)
            ->where('paid_amount', '>', 0)
            ->sum('paid_amount');
            
        $totalPiutangDiterima = Receivable::whereYear('paid_date', $this->tahun)
            ->where('paid_amount', '>', 0)
            ->sum('paid_amount');

        // Debug logging
        Log::info('ReportTahunan Debug:', [
            'tahun' => $this->tahun,
            'totalUtangDibayar' => $totalUtangDibayar,
            'totalPiutangDiterima' => $totalPiutangDiterima,
            'debt_count' => Debt::whereYear('paid_date', $this->tahun)->count(),
            'debt_paid_count' => Debt::whereYear('paid_date', $this->tahun)->where('paid_amount', '>', 0)->count(),
            'receivable_count' => Receivable::whereYear('paid_date', $this->tahun)->count(),
            'receivable_paid_count' => Receivable::whereYear('paid_date', $this->tahun)->where('paid_amount', '>', 0)->count(),
        ]);

        $this->yearlySummary = [
            'pendapatan' => $totalPendapatan,
            'pengeluaran_variabel' => $totalPengeluaranVariabel,
            'pengeluaran_tetap' => $totalPengeluaranTetap,
            'pengeluaran' => $totalPengeluaran,
            'laba' => $totalLaba,
            'utang_dibayar' => $totalUtangDibayar,
            'piutang_diterima' => $totalPiutangDiterima,
        ];

        // 3. Comprehensive Comparison with Previous Year
        $tahunSebelumnya = $this->tahun - 1;
        
        // Financial data for previous year
        $pendapatanSebelumnya = Income::whereYear('tanggal', $tahunSebelumnya)->sum(DB::raw('jumlah_terjual * harga_satuan'));
        $pengeluaranVariabelSebelumnya = Expenditure::whereYear('tanggal', $tahunSebelumnya)->sum('jumlah');
        $pengeluaranTetapSebelumnya = FixedCost::whereYear('tanggal', $tahunSebelumnya)->sum('nominal');
        $pengeluaranSebelumnya = $pengeluaranVariabelSebelumnya + $pengeluaranTetapSebelumnya;
        $labaSebelumnya = $pendapatanSebelumnya - $pengeluaranSebelumnya;

        // Debt & Receivable data for previous year
        $utangDibayarSebelumnya = Debt::whereYear('paid_date', $tahunSebelumnya)
            ->where('paid_amount', '>', 0)
            ->sum('paid_amount');
            
        $piutangDiterimaSebelumnya = Receivable::whereYear('paid_date', $tahunSebelumnya)
            ->where('paid_amount', '>', 0)
            ->sum('paid_amount');

        // Calculate growth percentages
        $growthData = [
            'pendapatan' => $this->calculateGrowthPercentage($pendapatanSebelumnya, $totalPendapatan),
            'pengeluaran_variabel' => $this->calculateGrowthPercentage($pengeluaranVariabelSebelumnya, $totalPengeluaranVariabel),
            'pengeluaran_tetap' => $this->calculateGrowthPercentage($pengeluaranTetapSebelumnya, $totalPengeluaranTetap),
            'pengeluaran' => $this->calculateGrowthPercentage($pengeluaranSebelumnya, $totalPengeluaran),
            'laba' => $this->calculateGrowthPercentage($labaSebelumnya, $totalLaba),
            'utang_dibayar' => $this->calculateGrowthPercentage($utangDibayarSebelumnya, $totalUtangDibayar),
            'piutang_diterima' => $this->calculateGrowthPercentage($piutangDiterimaSebelumnya, $totalPiutangDiterima),
        ];

        $this->comparisonData = [
            'current_year' => [
                'tahun' => $this->tahun,
                'pendapatan' => $totalPendapatan,
                'pengeluaran_variabel' => $totalPengeluaranVariabel,
                'pengeluaran_tetap' => $totalPengeluaranTetap,
                'pengeluaran' => $totalPengeluaran,
                'laba' => $totalLaba,
                'utang_dibayar' => $totalUtangDibayar,
                'piutang_diterima' => $totalPiutangDiterima,
            ],
            'previous_year' => [
                'tahun' => $tahunSebelumnya,
                'pendapatan' => $pendapatanSebelumnya,
                'pengeluaran_variabel' => $pengeluaranVariabelSebelumnya,
                'pengeluaran_tetap' => $pengeluaranTetapSebelumnya,
                'pengeluaran' => $pengeluaranSebelumnya,
                'laba' => $labaSebelumnya,
                'utang_dibayar' => $utangDibayarSebelumnya,
                'piutang_diterima' => $piutangDiterimaSebelumnya,
            ],
            'growth' => $growthData,
            'summary' => [
                'total_improvement_indicators' => count(array_filter($growthData, function($growth) { 
                    return $growth > 0; 
                })),
                'total_decline_indicators' => count(array_filter($growthData, function($growth) { 
                    return $growth < 0; 
                })),
                'overall_performance' => $this->assessOverallPerformance($growthData),
            ]
        ];

        // 4. Additional Stats
        $labaPerBulan = array_column($this->laporan, 'laba');
        $bulanLabaTertinggi = !empty($labaPerBulan) ? (array_keys($labaPerBulan, max($labaPerBulan))[0] + 1) : null;

        $this->stats = [
            'rata_rata_pendapatan' => $totalPendapatan > 0 ? $totalPendapatan / 12 : 0,
            'rata_rata_pengeluaran_variabel' => $totalPengeluaranVariabel > 0 ? $totalPengeluaranVariabel / 12 : 0,
            'rata_rata_pengeluaran_tetap' => $totalPengeluaranTetap > 0 ? $totalPengeluaranTetap / 12 : 0,
            'bulan_laba_tertinggi' => $bulanLabaTertinggi ? Carbon::create()->month($bulanLabaTertinggi)->translatedFormat('F') : 'N/A',
            'laba_tertinggi' => !empty($labaPerBulan) ? max($labaPerBulan) : 0,
        ];
        
        // 5. Chart Data
        $this->chartData = [
            'categories' => array_values(array_column($this->laporan, 'bulan')),
            'pendapatan' => array_values(array_column($this->laporan, 'pendapatan')),
            'pengeluaran_variabel' => array_values(array_column($this->laporan, 'pengeluaran_variabel')),
            'pengeluaran_tetap' => array_values(array_column($this->laporan, 'pengeluaran_tetap')),
            'pengeluaran' => array_values(array_column($this->laporan, 'pengeluaran')),
            'laba' => array_values(array_column($this->laporan, 'laba')),
            'utang_dibayar' => array_values(array_column($this->laporan, 'utang_dibayar')),
            'piutang_diterima' => array_values(array_column($this->laporan, 'piutang_diterima')),
        ];

        Log::info('Chart Data:', $this->chartData);
        Log::info('Comparison Data:', $this->comparisonData);

        // 6. Load comprehensive debt and receivable data
        $this->loadDebtReceivableData();

        $this->dispatch('reportUpdated', ['chartData' => $this->chartData, 'comparisonData' => $this->comparisonData]);
    }

    /**
     * Load comprehensive debt and receivable data for the year
     */
    private function loadDebtReceivableData()
    {
        try {
            // 1. Status utang dan piutang saat ini (akhir tahun)
            $endOfYear = Carbon::create($this->tahun, 12, 31)->endOfDay();
            $startOfYear = Carbon::create($this->tahun, 1, 1)->startOfDay();

            // Utang yang masih aktif di akhir tahun
            $currentActiveDebts = Debt::where(function($query) use ($endOfYear) {
                    $query->where('created_at', '<=', $endOfYear)
                          ->where(function($subQuery) use ($endOfYear) {
                              $subQuery->whereNull('paid_date')
                                       ->orWhere('paid_date', '>', $endOfYear);
                          });
                })
                ->where('status', '!=', 'paid')
                ->get();

            // Piutang yang masih aktif di akhir tahun
            $currentActiveReceivables = Receivable::where(function($query) use ($endOfYear) {
                    $query->where('created_at', '<=', $endOfYear)
                          ->where(function($subQuery) use ($endOfYear) {
                              $subQuery->whereNull('paid_date')
                                       ->orWhere('paid_date', '>', $endOfYear);
                          });
                })
                ->where('status', '!=', 'paid')
                ->get();

            // 2. Ringkasan transaksi utang piutang per bulan
            $monthlyDebtReceivableData = [];
            for ($bulan = 1; $bulan <= 12; $bulan++) {
                // Utang baru yang dibuat dalam bulan ini
                $newDebts = Debt::whereYear('created_at', $this->tahun)
                    ->whereMonth('created_at', $bulan)
                    ->sum('amount');

                // Piutang baru yang dibuat dalam bulan ini
                $newReceivables = Receivable::whereYear('created_at', $this->tahun)
                    ->whereMonth('created_at', $bulan)
                    ->sum('amount');

                // Utang yang dibayar dalam bulan ini
                $paidDebts = Debt::whereYear('paid_date', $this->tahun)
                    ->whereMonth('paid_date', $bulan)
                    ->whereNotNull('paid_date')
                    ->sum('paid_amount');

                // Piutang yang diterima dalam bulan ini
                $receivedReceivables = Receivable::whereYear('paid_date', $this->tahun)
                    ->whereMonth('paid_date', $bulan)
                    ->whereNotNull('paid_date')
                    ->sum('paid_amount');

                $monthlyDebtReceivableData[$bulan] = [
                    'bulan' => Carbon::create()->month($bulan)->translatedFormat('F'),
                    'utang_baru' => $newDebts,
                    'piutang_baru' => $newReceivables,
                    'utang_dibayar' => $paidDebts,
                    'piutang_diterima' => $receivedReceivables,
                ];
            }

            // 3. Status saat ini
            $this->currentDebtReceivableStatus = [
                'total_utang_aktif' => $currentActiveDebts->sum('remaining_amount'),
                'total_piutang_aktif' => $currentActiveReceivables->sum('remaining_amount'),
                'jumlah_utang_aktif' => $currentActiveDebts->count(),
                'jumlah_piutang_aktif' => $currentActiveReceivables->count(),
                'utang_overdue' => $currentActiveDebts->where('is_overdue', true)->count(),
                'piutang_overdue' => $currentActiveReceivables->where('is_overdue', true)->count(),
                'total_utang_overdue' => $currentActiveDebts->where('is_overdue', true)->sum('remaining_amount'),
                'total_piutang_overdue' => $currentActiveReceivables->where('is_overdue', true)->sum('remaining_amount'),
            ];

            // 4. Data untuk chart dan analisis
            $this->debtReceivableData = [
                'monthly_data' => $monthlyDebtReceivableData,
                'yearly_summary' => [
                    'total_utang_baru' => array_sum(array_column($monthlyDebtReceivableData, 'utang_baru')),
                    'total_piutang_baru' => array_sum(array_column($monthlyDebtReceivableData, 'piutang_baru')),
                    'total_utang_dibayar' => array_sum(array_column($monthlyDebtReceivableData, 'utang_dibayar')),
                    'total_piutang_diterima' => array_sum(array_column($monthlyDebtReceivableData, 'piutang_diterima')),
                ],
                'current_status' => $this->currentDebtReceivableStatus,
            ];

            // Update chart data untuk include debt & receivable trends
            $this->chartData['utang_baru'] = array_values(array_column($monthlyDebtReceivableData, 'utang_baru'));
            $this->chartData['piutang_baru'] = array_values(array_column($monthlyDebtReceivableData, 'piutang_baru'));

            Log::info('Debt Receivable Data loaded for year ' . $this->tahun, [
                'current_status' => $this->currentDebtReceivableStatus,
                'yearly_summary' => $this->debtReceivableData['yearly_summary']
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading debt receivable data: ' . $e->getMessage(), [
                'tahun' => $this->tahun,
                'trace' => $e->getTraceAsString()
            ]);

            // Reset data jika error
            $this->debtReceivableData = [];
            $this->currentDebtReceivableStatus = [
                'total_utang_aktif' => 0,
                'total_piutang_aktif' => 0,
                'jumlah_utang_aktif' => 0,
                'jumlah_piutang_aktif' => 0,
                'utang_overdue' => 0,
                'piutang_overdue' => 0,
                'total_utang_overdue' => 0,
                'total_piutang_overdue' => 0,
            ];
        }
    }

    /**
     * Calculate growth percentage between two values
     */
    private function calculateGrowthPercentage($previousValue, $currentValue)
    {
        if ($previousValue == 0) {
            return $currentValue > 0 ? 100 : 0;
        }
        
        return round((($currentValue - $previousValue) / $previousValue) * 100, 2);
    }

    /**
     * Assess overall performance based on growth data
     */
    private function assessOverallPerformance($growthData)
    {
        // Weight different metrics for overall assessment
        $weights = [
            'pendapatan' => 0.3,        // Revenue growth is most important
            'laba' => 0.25,             // Profit growth is very important
            'pengeluaran' => -0.15,     // Lower expense growth is good (negative weight)
            'pengeluaran_variabel' => -0.1,
            'pengeluaran_tetap' => -0.05,
            'utang_dibayar' => 0.1,     // Paying more debt is good
            'piutang_diterima' => 0.05, // Collecting more receivables is good
        ];

        $weightedScore = 0;
        $totalWeight = 0;

        foreach ($weights as $metric => $weight) {
            if (isset($growthData[$metric])) {
                $weightedScore += $growthData[$metric] * $weight;
                $totalWeight += abs($weight);
            }
        }

        $averageScore = $totalWeight > 0 ? $weightedScore / $totalWeight : 0;

        // Determine performance level
        if ($averageScore >= 15) {
            return [
                'level' => 'excellent',
                'label' => 'Sangat Baik',
                'color' => 'success',
                'icon' => 'bi-trophy',
                'description' => 'Performa bisnis sangat baik dengan pertumbuhan signifikan'
            ];
        } elseif ($averageScore >= 5) {
            return [
                'level' => 'good',
                'label' => 'Baik',
                'color' => 'primary',
                'icon' => 'bi-graph-up-arrow',
                'description' => 'Performa bisnis baik dengan tren positif'
            ];
        } elseif ($averageScore >= -5) {
            return [
                'level' => 'stable',
                'label' => 'Stabil',
                'color' => 'warning',
                'icon' => 'bi-dash-circle',
                'description' => 'Performa bisnis stabil dengan sedikit perubahan'
            ];
        } else {
            return [
                'level' => 'needs_attention',
                'label' => 'Perlu Perhatian',
                'color' => 'danger',
                'icon' => 'bi-exclamation-triangle',
                'description' => 'Performa bisnis memerlukan perhatian dan perbaikan'
            ];
        }
    }

    public function render()
    {
        return view('livewire.reports.reporttahunan-list');
    }
}
