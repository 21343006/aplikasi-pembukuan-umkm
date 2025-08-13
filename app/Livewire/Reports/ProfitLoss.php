<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\Income;
use App\Models\Expenditure;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfitLoss extends Component
{
    #[Title('Laporan Rugi Laba')]

    // Properties dengan type declaration yang benar
    public string $selectedYear = '';
    public string $selectedMonth = '';
    public bool $showDetails = false;
    public string $reportType = 'yearly';

    // Array nama bulan dalam bahasa Indonesia
    public array $monthNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public function mount(): void
    {
        $this->selectedYear = (string) now()->year;
        $this->reportType = 'yearly';
        $this->selectedMonth = '';
        
        // Debug log
        Log::info('ProfitLoss mounted', [
            'user_id' => Auth::id(),
            'selectedYear' => $this->selectedYear,
            'reportType' => $this->reportType
        ]);
    }

    public function updatedSelectedYear(): void
    {
        if ($this->reportType === 'monthly') {
            $this->selectedMonth = '';
        }
        Log::info('Year updated', ['selectedYear' => $this->selectedYear]);
    }

    public function updatedReportType(): void
    {
        switch($this->reportType) {
            case 'all':
                $this->selectedYear = '';
                $this->selectedMonth = '';
                break;
            case 'yearly':
                $this->selectedYear = (string) now()->year;
                $this->selectedMonth = '';
                break;
            case 'monthly':
                $this->selectedYear = (string) now()->year;
                $this->selectedMonth = '';
                break;
        }
        Log::info('Report type updated', ['reportType' => $this->reportType]);
    }

    public function toggleDetails(): void
    {
        $this->showDetails = !$this->showDetails;
    }

    private function hasValidMonthSelection(): bool
    {
        return !empty($this->selectedMonth) && 
               is_numeric($this->selectedMonth) && 
               (int)$this->selectedMonth >= 1 && 
               (int)$this->selectedMonth <= 12;
    }

    /**
     * PERBAIKAN UTAMA: Query yang lebih robust untuk total pendapatan
     */
    public function getTotalPendapatan(): float
    {
        try {
            if (!Auth::check()) {
                Log::warning('User not authenticated in getTotalPendapatan');
                return 0.0;
            }

            // PERBAIKAN: Gunakan query builder tanpa global scope yang bermasalah
            $query = DB::table('incomes')->where('user_id', Auth::id());

            // Debug: Log jumlah total record user
            $totalRecords = DB::table('incomes')->where('user_id', Auth::id())->count();
            Log::info('Total income records for user', ['count' => $totalRecords, 'user_id' => Auth::id()]);

            // Apply filters berdasarkan report type
            if ($this->reportType === 'yearly' && !empty($this->selectedYear) && is_numeric($this->selectedYear)) {
                $query->whereYear('tanggal', (int)$this->selectedYear);
                Log::info('Applied yearly filter', ['year' => $this->selectedYear]);
            } elseif ($this->reportType === 'monthly' && !empty($this->selectedYear) && $this->hasValidMonthSelection()) {
                $query->whereYear('tanggal', (int)$this->selectedYear)
                      ->whereMonth('tanggal', (int)$this->selectedMonth);
                Log::info('Applied monthly filter', ['year' => $this->selectedYear, 'month' => $this->selectedMonth]);
            }

            // Debug: Log filtered count
            $filteredCount = (clone $query)->count();
            Log::info('Filtered income records', ['count' => $filteredCount]);

            // PERBAIKAN: Hitung total dengan handling null values yang lebih baik
            $result = $query->selectRaw('
                COALESCE(
                    SUM(
                        CAST(IFNULL(jumlah_terjual, 0) AS DECIMAL(15,2)) * 
                        CAST(IFNULL(harga_satuan, 0) AS DECIMAL(15,2))
                    ), 
                    0
                ) as total
            ')->first();

            $total = (float)($result->total ?? 0);
            
            Log::info('Calculated total pendapatan', [
                'total' => $total,
                'reportType' => $this->reportType,
                'selectedYear' => $this->selectedYear,
                'selectedMonth' => $this->selectedMonth
            ]);

            return $total;

        } catch (\Exception $e) {
            Log::error('Error calculating total pendapatan: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'reportType' => $this->reportType,
                'selectedYear' => $this->selectedYear,
                'selectedMonth' => $this->selectedMonth,
                'trace' => $e->getTraceAsString()
            ]);
            return 0.0;
        }
    }

    public function getTotalPengeluaran(): float
    {
        try {
            if (!Auth::check()) {
                Log::warning('User not authenticated in getTotalPengeluaran');
                return 0.0;
            }

            // PERBAIKAN: Gunakan query builder langsung
            $query = DB::table('expenditures')->where('user_id', Auth::id());

            // Debug: Log jumlah total record user
            $totalRecords = DB::table('expenditures')->where('user_id', Auth::id())->count();
            Log::info('Total expenditure records for user', ['count' => $totalRecords, 'user_id' => Auth::id()]);

            // Apply filters
            if ($this->reportType === 'yearly' && !empty($this->selectedYear) && is_numeric($this->selectedYear)) {
                $query->whereYear('tanggal', (int)$this->selectedYear);
            } elseif ($this->reportType === 'monthly' && !empty($this->selectedYear) && $this->hasValidMonthSelection()) {
                $query->whereYear('tanggal', (int)$this->selectedYear)
                      ->whereMonth('tanggal', (int)$this->selectedMonth);
            }

            // Debug: Log filtered count
            $filteredCount = (clone $query)->count();
            Log::info('Filtered expenditure records', ['count' => $filteredCount]);

            // Hitung total
            $result = $query->selectRaw('
                COALESCE(
                    SUM(CAST(IFNULL(jumlah, 0) AS DECIMAL(15,2))), 
                    0
                ) as total
            ')->first();

            $total = (float)($result->total ?? 0);
            
            Log::info('Calculated total pengeluaran', [
                'total' => $total,
                'reportType' => $this->reportType,
                'selectedYear' => $this->selectedYear,
                'selectedMonth' => $this->selectedMonth
            ]);

            return $total;

        } catch (\Exception $e) {
            Log::error('Error calculating total pengeluaran: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'reportType' => $this->reportType,
                'selectedYear' => $this->selectedYear,
                'selectedMonth' => $this->selectedMonth,
                'trace' => $e->getTraceAsString()
            ]);
            return 0.0;
        }
    }

    public function getLabaRugi(): float
    {
        $pendapatan = $this->getTotalPendapatan();
        $pengeluaran = $this->getTotalPengeluaran();
        $labaRugi = $pendapatan - $pengeluaran;
        
        Log::info('Calculated laba rugi', [
            'pendapatan' => $pendapatan,
            'pengeluaran' => $pengeluaran,
            'labaRugi' => $labaRugi
        ]);
        
        return $labaRugi;
    }

    public function getMarginProfit(): float
    {
        $totalPendapatan = $this->getTotalPendapatan();
        if ($totalPendapatan > 0) {
            $margin = ($this->getLabaRugi() / $totalPendapatan) * 100;
            Log::info('Calculated margin profit', ['margin' => $margin]);
            return $margin;
        }
        return 0.0;
    }

    /**
     * PERBAIKAN: Yearly data dengan query builder langsung
     */
    public function getYearlyData(): array
    {
        try {
            if (!Auth::check()) {
                return [];
            }

            $years = range(now()->year, 2020);
            $yearlyData = [];

            foreach ($years as $year) {
                // PERBAIKAN: Query pendapatan per tahun dengan DB query builder
                $pendapatanResult = DB::table('incomes')
                    ->where('user_id', Auth::id())
                    ->whereYear('tanggal', $year)
                    ->selectRaw('
                        COALESCE(
                            SUM(
                                CAST(IFNULL(jumlah_terjual, 0) AS DECIMAL(15,2)) * 
                                CAST(IFNULL(harga_satuan, 0) AS DECIMAL(15,2))
                            ), 
                            0
                        ) as total
                    ')
                    ->first();

                $pengeluaranResult = DB::table('expenditures')
                    ->where('user_id', Auth::id())
                    ->whereYear('tanggal', $year)
                    ->selectRaw('
                        COALESCE(
                            SUM(CAST(IFNULL(jumlah, 0) AS DECIMAL(15,2))), 
                            0
                        ) as total
                    ')
                    ->first();

                $pendapatan = (float)($pendapatanResult->total ?? 0);
                $pengeluaran = (float)($pengeluaranResult->total ?? 0);
                
                $labaRugi = $pendapatan - $pengeluaran;
                $margin = $pendapatan > 0 ? ($labaRugi / $pendapatan) * 100 : 0;

                // Hanya tampilkan tahun yang memiliki data
                if ($pendapatan > 0 || $pengeluaran > 0) {
                    $yearlyData[] = [
                        'year' => $year,
                        'pendapatan' => $pendapatan,
                        'pengeluaran' => $pengeluaran,
                        'laba_rugi' => $labaRugi,
                        'margin' => $margin,
                    ];
                }
            }

            Log::info('Generated yearly data', ['count' => count($yearlyData)]);
            return $yearlyData;
        } catch (\Exception $e) {
            Log::error('Error getting yearly data: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    /**
     * PERBAIKAN: Monthly data dengan query builder langsung
     */
    public function getMonthlyData(): array
    {
        try {
            if (!Auth::check() || empty($this->selectedYear) || !is_numeric($this->selectedYear)) {
                Log::info('Invalid conditions for monthly data', [
                    'auth' => Auth::check(),
                    'selectedYear' => $this->selectedYear
                ]);
                return [];
            }

            $monthlyData = [];
            $year = (int)$this->selectedYear;

            for ($month = 1; $month <= 12; $month++) {
                // Query pendapatan per bulan
                $pendapatanResult = DB::table('incomes')
                    ->where('user_id', Auth::id())
                    ->whereYear('tanggal', $year)
                    ->whereMonth('tanggal', $month)
                    ->selectRaw('
                        COALESCE(
                            SUM(
                                CAST(IFNULL(jumlah_terjual, 0) AS DECIMAL(15,2)) * 
                                CAST(IFNULL(harga_satuan, 0) AS DECIMAL(15,2))
                            ), 
                            0
                        ) as total
                    ')
                    ->first();

                $pengeluaranResult = DB::table('expenditures')
                    ->where('user_id', Auth::id())
                    ->whereYear('tanggal', $year)
                    ->whereMonth('tanggal', $month)
                    ->selectRaw('
                        COALESCE(
                            SUM(CAST(IFNULL(jumlah, 0) AS DECIMAL(15,2))), 
                            0
                        ) as total
                    ')
                    ->first();

                $pendapatan = (float)($pendapatanResult->total ?? 0);
                $pengeluaran = (float)($pengeluaranResult->total ?? 0);
                
                $labaRugi = $pendapatan - $pengeluaran;
                $margin = $pendapatan > 0 ? ($labaRugi / $pendapatan) * 100 : 0;

                // Hanya tampilkan bulan yang memiliki data
                if ($pendapatan > 0 || $pengeluaran > 0) {
                    $monthlyData[] = [
                        'month' => $month,
                        'month_name' => $this->monthNames[$month],
                        'pendapatan' => $pendapatan,
                        'pengeluaran' => $pengeluaran,
                        'laba_rugi' => $labaRugi,
                        'margin' => $margin,
                    ];
                }
            }

            Log::info('Generated monthly data', [
                'year' => $year,
                'count' => count($monthlyData)
            ]);
            return $monthlyData;
        } catch (\Exception $e) {
            Log::error('Error getting monthly data: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'selectedYear' => $this->selectedYear,
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    public function getGrowthRate(): ?float
    {
        try {
            if (!Auth::check() || empty($this->selectedYear) || !is_numeric($this->selectedYear)) {
                return null;
            }

            $currentYear = (int) $this->selectedYear;
            $previousYear = $currentYear - 1;

            // Query pendapatan tahun ini dan sebelumnya
            $currentIncomeResult = DB::table('incomes')
                ->where('user_id', Auth::id())
                ->whereYear('tanggal', $currentYear)
                ->selectRaw('
                    COALESCE(
                        SUM(
                            CAST(IFNULL(jumlah_terjual, 0) AS DECIMAL(15,2)) * 
                            CAST(IFNULL(harga_satuan, 0) AS DECIMAL(15,2))
                        ), 
                        0
                    ) as total
                ')
                ->first();

            $previousIncomeResult = DB::table('incomes')
                ->where('user_id', Auth::id())
                ->whereYear('tanggal', $previousYear)
                ->selectRaw('
                    COALESCE(
                        SUM(
                            CAST(IFNULL(jumlah_terjual, 0) AS DECIMAL(15,2)) * 
                            CAST(IFNULL(harga_satuan, 0) AS DECIMAL(15,2))
                        ), 
                        0
                    ) as total
                ')
                ->first();

            $currentIncome = (float)($currentIncomeResult->total ?? 0);
            $previousIncome = (float)($previousIncomeResult->total ?? 0);

            Log::info('Growth rate calculation', [
                'currentYear' => $currentYear,
                'previousYear' => $previousYear,
                'currentIncome' => $currentIncome,
                'previousIncome' => $previousIncome
            ]);

            if ($previousIncome > 0) {
                return (($currentIncome - $previousIncome) / $previousIncome) * 100;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Error calculating growth rate: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'selectedYear' => $this->selectedYear,
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * PERBAIKAN: Chart data dengan query builder langsung
     */
    public function getChartData(): array
    {
        try {
            if (!Auth::check()) {
                return [
                    'labels' => [],
                    'pendapatan' => [],
                    'pengeluaran' => [],
                    'profit' => []
                ];
            }

            if ($this->reportType === 'yearly' && !empty($this->selectedYear) && is_numeric($this->selectedYear)) {
                return $this->getMonthlyChartData();
            } elseif ($this->reportType === 'all' || ($this->reportType === 'yearly' && empty($this->selectedYear))) {
                return $this->getYearlyChartData();
            }

            return [
                'labels' => [],
                'pendapatan' => [],
                'pengeluaran' => [],
                'profit' => []
            ];

        } catch (\Exception $e) {
            Log::error('Error getting chart data: ' . $e->getMessage());
            return [
                'labels' => [],
                'pendapatan' => [],
                'pengeluaran' => [],
                'profit' => []
            ];
        }
    }

    private function getMonthlyChartData(): array
    {
        $labels = [];
        $pendapatanData = [];
        $pengeluaranData = [];
        $profitData = [];
        $year = (int)$this->selectedYear;

        for ($month = 1; $month <= 12; $month++) {
            // Query pendapatan bulanan
            $pendapatanResult = DB::table('incomes')
                ->where('user_id', Auth::id())
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->selectRaw('
                    COALESCE(
                        SUM(
                            CAST(IFNULL(jumlah_terjual, 0) AS DECIMAL(15,2)) * 
                            CAST(IFNULL(harga_satuan, 0) AS DECIMAL(15,2))
                        ), 
                        0
                    ) as total
                ')
                ->first();

            $pengeluaranResult = DB::table('expenditures')
                ->where('user_id', Auth::id())
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->selectRaw('
                    COALESCE(
                        SUM(CAST(IFNULL(jumlah, 0) AS DECIMAL(15,2))), 
                        0
                    ) as total
                ')
                ->first();

            $pendapatan = (float)($pendapatanResult->total ?? 0);
            $pengeluaran = (float)($pengeluaranResult->total ?? 0);
            $profit = $pendapatan - $pengeluaran;

            $labels[] = substr($this->monthNames[$month], 0, 3); // Jan, Feb, etc.
            $pendapatanData[] = $pendapatan;
            $pengeluaranData[] = $pengeluaran;
            $profitData[] = $profit;
        }

        Log::info('Generated monthly chart data', [
            'year' => $year,
            'totalPendapatan' => array_sum($pendapatanData),
            'totalPengeluaran' => array_sum($pengeluaranData)
        ]);

        return [
            'labels' => $labels,
            'pendapatan' => $pendapatanData,
            'pengeluaran' => $pengeluaranData,
            'profit' => $profitData
        ];
    }

    private function getYearlyChartData(): array
    {
        $labels = [];
        $pendapatanData = [];
        $pengeluaranData = [];
        $profitData = [];

        $years = range(now()->year, max(2020, now()->year - 4)); // Last 5 years

        foreach ($years as $year) {
            // Query pendapatan tahunan
            $pendapatanResult = DB::table('incomes')
                ->where('user_id', Auth::id())
                ->whereYear('tanggal', $year)
                ->selectRaw('
                    COALESCE(
                        SUM(
                            CAST(IFNULL(jumlah_terjual, 0) AS DECIMAL(15,2)) * 
                            CAST(IFNULL(harga_satuan, 0) AS DECIMAL(15,2))
                        ), 
                        0
                    ) as total
                ')
                ->first();

            $pengeluaranResult = DB::table('expenditures')
                ->where('user_id', Auth::id())
                ->whereYear('tanggal', $year)
                ->selectRaw('
                    COALESCE(
                        SUM(CAST(IFNULL(jumlah, 0) AS DECIMAL(15,2))), 
                        0
                    ) as total
                ')
                ->first();

            $pendapatan = (float)($pendapatanResult->total ?? 0);
            $pengeluaran = (float)($pengeluaranResult->total ?? 0);
            $profit = $pendapatan - $pengeluaran;

            $labels[] = (string)$year;
            $pendapatanData[] = $pendapatan;
            $pengeluaranData[] = $pengeluaran;
            $profitData[] = $profit;
        }

        return [
            'labels' => array_reverse($labels),
            'pendapatan' => array_reverse($pendapatanData),
            'pengeluaran' => array_reverse($pengeluaranData),
            'profit' => array_reverse($profitData)
        ];
    }

    public function exportData(): ?\Symfony\Component\HttpFoundation\StreamedResponse
    {
        try {
            if (!Auth::check()) {
                session()->flash('error', 'Anda harus login terlebih dahulu.');
                return null;
            }

            $data = [];
            $fileName = '';
            $headers = [];

            if ($this->reportType === 'yearly') {
                $data = $this->getYearlyData();
                $fileName = 'laporan_rugi_laba_tahunan.csv';
                $headers = ['Tahun', 'Pendapatan', 'Pengeluaran', 'Laba/Rugi', 'Margin (%)'];
            } elseif ($this->reportType === 'monthly' && !empty($this->selectedYear)) {
                $data = $this->getMonthlyData();
                $fileName = "laporan_rugi_laba_bulanan_{$this->selectedYear}.csv";
                $headers = ['Bulan', 'Pendapatan', 'Pengeluaran', 'Laba/Rugi', 'Margin (%)'];
            } else {
                session()->flash('error', 'Silakan pilih jenis laporan dan periode yang valid.');
                return null;
            }

            if (empty($data)) {
                session()->flash('error', 'Tidak ada data untuk di-export.');
                return null;
            }

            $csvHeaders = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
                'Pragma' => 'public',
            ];

            $callback = function() use ($data, $headers) {
                $file = fopen('php://output', 'w');
                
                if ($file === false) {
                    return;
                }
                
                // Add BOM untuk support Unicode di Excel
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

                // Header kolom
                fputcsv($file, $headers, ';');

                // Data rows
                foreach ($data as $row) {
                    if ($this->reportType === 'yearly') {
                        fputcsv($file, [
                            $row['year'],
                            number_format($row['pendapatan'], 0, ',', '.'),
                            number_format($row['pengeluaran'], 0, ',', '.'),
                            number_format($row['laba_rugi'], 0, ',', '.'),
                            number_format($row['margin'], 2, ',', '.') . '%'
                        ], ';');
                    } else {
                        fputcsv($file, [
                            $row['month_name'],
                            number_format($row['pendapatan'], 0, ',', '.'),
                            number_format($row['pengeluaran'], 0, ',', '.'),
                            number_format($row['laba_rugi'], 0, ',', '.'),
                            number_format($row['margin'], 2, ',', '.') . '%'
                        ], ';');
                    }
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $csvHeaders);

        } catch (\Exception $e) {
            Log::error('Error in exportData: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'reportType' => $this->reportType,
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Terjadi kesalahan saat export data.');
            return null;
        }
    }

    /**
     * PERBAIKAN: Available years dengan query builder langsung
     */
    public function getAvailableYears(): array
    {
        try {
            if (!Auth::check()) {
                return [];
            }

            // Query untuk mendapatkan tahun yang tersedia dari kedua tabel
            $incomeYears = DB::table('incomes')
                ->where('user_id', Auth::id())
                ->selectRaw('DISTINCT YEAR(tanggal) as year')
                ->whereNotNull('tanggal')
                ->pluck('year')
                ->toArray();

            $expenditureYears = DB::table('expenditures')
                ->where('user_id', Auth::id())
                ->selectRaw('DISTINCT YEAR(tanggal) as year')
                ->whereNotNull('tanggal')
                ->pluck('year')
                ->toArray();

            $allYears = array_unique(array_merge($incomeYears, $expenditureYears));
            rsort($allYears); // Sort descending

            Log::info('Available years', ['years' => $allYears]);
            return $allYears;
        } catch (\Exception $e) {
            Log::error('Error getting available years: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            return [now()->year];
        }
    }

    public function getIsMonthSelected(): bool
    {
        return $this->reportType === 'monthly' && 
               !empty($this->selectedYear) && 
               $this->hasValidMonthSelection();
    }

    public function getSelectedMonthName(): string
    {
        if ($this->hasValidMonthSelection()) {
            return $this->monthNames[(int)$this->selectedMonth] ?? 'Bulan';
        }
        return 'Bulan';
    }

    /**
     * PERBAIKAN UTAMA: Render method yang lebih robust dengan error handling
     */
    public function render()
    {
        try {
            Log::info('Render method called', [
                'reportType' => $this->reportType,
                'selectedYear' => $this->selectedYear,
                'selectedMonth' => $this->selectedMonth,
                'user_id' => Auth::id()
            ]);

            // PERBAIKAN: Pastikan user login sebelum proses apapun
            if (!Auth::check()) {
                Log::warning('User not authenticated in render');
                return view('livewire.reports.profit-loss', [
                    'totalPendapatan' => 0.0,
                    'totalPengeluaran' => 0.0,
                    'labaRugi' => 0.0,
                    'marginProfit' => 0.0,
                    'growthRate' => null,
                    'yearlyData' => [],
                    'monthlyData' => [],
                    'availableYears' => [now()->year],
                    'monthNames' => $this->monthNames,
                    'isMonthSelected' => false,
                    'selectedMonthName' => 'Bulan',
                    'chartData' => [
                        'labels' => [],
                        'pendapatan' => [],
                        'pengeluaran' => [],
                        'profit' => []
                    ],
                ]);
            }

            // Ambil data dengan safe method calls
            $totalPendapatan = $this->getTotalPendapatan();
            $totalPengeluaran = $this->getTotalPengeluaran();
            $labaRugi = $totalPendapatan - $totalPengeluaran; // Hitung langsung di sini untuk memastikan
            $marginProfit = $this->getMarginProfit();
            
            // Data yang lebih kompleks dengan error handling
            $growthRate = null;
            $yearlyData = [];
            $monthlyData = [];
            $chartData = [
                'labels' => [],
                'pendapatan' => [],
                'pengeluaran' => [],
                'profit' => []
            ];

            try {
                $growthRate = $this->getGrowthRate();
            } catch (\Exception $e) {
                Log::error('Error getting growth rate in render: ' . $e->getMessage());
                $growthRate = null;
            }

            try {
                $yearlyData = $this->getYearlyData();
            } catch (\Exception $e) {
                Log::error('Error getting yearly data in render: ' . $e->getMessage());
                $yearlyData = [];
            }

            try {
                $monthlyData = $this->getMonthlyData();
            } catch (\Exception $e) {
                Log::error('Error getting monthly data in render: ' . $e->getMessage());
                $monthlyData = [];
            }

            try {
                $chartData = $this->getChartData();
            } catch (\Exception $e) {
                Log::error('Error getting chart data in render: ' . $e->getMessage());
                $chartData = [
                    'labels' => [],
                    'pendapatan' => [],
                    'pengeluaran' => [],
                    'profit' => []
                ];
            }

            $availableYears = [];
            try {
                $availableYears = $this->getAvailableYears();
            } catch (\Exception $e) {
                Log::error('Error getting available years in render: ' . $e->getMessage());
                $availableYears = [now()->year];
            }

            Log::info('Render data prepared successfully', [
                'totalPendapatan' => $totalPendapatan,
                'totalPengeluaran' => $totalPengeluaran,
                'labaRugi' => $labaRugi,
                'yearlyDataCount' => count($yearlyData),
                'monthlyDataCount' => count($monthlyData),
                'availableYearsCount' => count($availableYears)
            ]);

            return view('livewire.reports.profit-loss', [
                'totalPendapatan' => $totalPendapatan,
                'totalPengeluaran' => $totalPengeluaran,
                'labaRugi' => $labaRugi,
                'marginProfit' => $marginProfit,
                'growthRate' => $growthRate,
                'yearlyData' => $yearlyData,
                'monthlyData' => $monthlyData,
                'availableYears' => $availableYears,
                'monthNames' => $this->monthNames,
                'isMonthSelected' => $this->getIsMonthSelected(),
                'selectedMonthName' => $this->getSelectedMonthName(),
                'chartData' => $chartData,
            ]);
        } catch (\Exception $e) {
            Log::error('Critical error in render method: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // PERBAIKAN: Return view dengan data kosong tapi aman ketika error
            return view('livewire.reports.profit-loss', [
                'totalPendapatan' => 0.0,
                'totalPengeluaran' => 0.0,
                'labaRugi' => 0.0,
                'marginProfit' => 0.0,
                'growthRate' => null,
                'yearlyData' => [],
                'monthlyData' => [],
                'availableYears' => [now()->year],
                'monthNames' => $this->monthNames,
                'isMonthSelected' => false,
                'selectedMonthName' => 'Bulan',
                'chartData' => [
                    'labels' => [],
                    'pendapatan' => [],
                    'pengeluaran' => [],
                    'profit' => []
                ],
            ]);
        }
    }

    // Method untuk debugging - bisa dipanggil dari view untuk testing
    public function debugData()
    {
        if (!Auth::check()) {
            session()->flash('error', 'User not authenticated');
            return;
        }

        // Debug info untuk troubleshooting
        $debugInfo = [
            'user_id' => Auth::id(),
            'reportType' => $this->reportType,
            'selectedYear' => $this->selectedYear,
            'selectedMonth' => $this->selectedMonth,
            'total_income_records' => DB::table('incomes')->where('user_id', Auth::id())->count(),
            'total_expenditure_records' => DB::table('expenditures')->where('user_id', Auth::id())->count(),
        ];

        // Ambil sample data
        $sampleIncome = DB::table('incomes')
            ->where('user_id', Auth::id())
            ->take(3)
            ->get(['id', 'tanggal', 'produk', 'jumlah_terjual', 'harga_satuan'])
            ->toArray();

        $sampleExpenditure = DB::table('expenditures')
            ->where('user_id', Auth::id())
            ->take(3)
            ->get(['id', 'tanggal', 'nama_pengeluaran', 'jumlah'])
            ->toArray();

        Log::info('Debug Data', [
            'debugInfo' => $debugInfo,
            'sampleIncome' => $sampleIncome,
            'sampleExpenditure' => $sampleExpenditure
        ]);

        session()->flash('message', 'Debug info telah ditulis ke log. Check storage/logs/laravel.log');
    }
}