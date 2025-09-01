<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\Income;
use App\Models\Expenditure;
use App\Models\FixedCost;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ProfitLoss extends Component
{
    #[Title('Laporan Rugi Laba')]

    // Core properties
    public $selectedYear = '';
    public $selectedMonth = '';
    public $showDetails = false;
    public $reportType = 'yearly';

    // Data arrays
    public array $yearlyData = [];
    public array $monthlyData = [];
    public array $dailyData = [];

    // Month names in Indonesian
    public array $monthNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public function mount()
    {
        try {
            $this->selectedYear = (string) now()->year;
            $this->reportType = 'yearly';
            $this->selectedMonth = '';
            $this->showDetails = false;
            $this->resetDataArrays();
            $this->loadData();

            Log::info('ProfitLoss mounted successfully', [
                'user_id' => Auth::id(),
                'selectedYear' => $this->selectedYear,
                'reportType' => $this->reportType
            ]);
        } catch (\Exception $e) {
            Log::error('Error in mount: ' . $e->getMessage());
            $this->resetToDefaults();
        }
    }

    private function resetToDefaults()
    {
        $this->selectedYear = (string) now()->year;
        $this->reportType = 'yearly';
        $this->selectedMonth = '';
        $this->showDetails = false;
        $this->resetDataArrays();
    }

    private function resetDataArrays()
    {
        $this->yearlyData = [];
        $this->monthlyData = [];
        $this->dailyData = [];
    }

    public function updatedReportType()
    {
        try {
            switch ($this->reportType) {
                case 'all':
                    $this->selectedYear = '';
                    $this->selectedMonth = '';
                    break;
                case 'yearly':
                    $this->selectedYear = $this->selectedYear ?: (string) now()->year;
                    $this->selectedMonth = '';
                    break;
                case 'monthly':
                    $this->selectedYear = $this->selectedYear ?: (string) now()->year;
                    $this->selectedMonth = '';
                    break;
            }

            $this->showDetails = false;
            $this->resetDataArrays();
            $this->loadData();

        } catch (\Exception $e) {
            Log::error('Error in updatedReportType: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat mengubah jenis laporan.');
            $this->resetToDefaults();
        }
    }

    public function updatedSelectedYear()
    {
        try {
            // Normalize input (trim spaces and ensure string form)
            $this->selectedYear = trim((string) $this->selectedYear);

            if ($this->reportType === 'monthly') {
                $this->selectedMonth = '';
            }

            if ($this->selectedYear && !is_numeric($this->selectedYear)) {
                $this->selectedYear = (string) now()->year;
                session()->flash('error', 'Tahun yang dipilih tidak valid.');
            }

            if ($this->selectedYear && ((int)$this->selectedYear < 2000 || (int)$this->selectedYear > 2099)) {
                $this->selectedYear = (string) now()->year;
                session()->flash('error', 'Tahun harus antara 2000-2099.');
            }

            $this->resetDataArrays();
            $this->loadData();

        } catch (\Exception $e) {
            Log::error('Error in updatedSelectedYear: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat mengubah tahun.');
        }
    }

    public function updatedSelectedMonth()
    {
        try {
            $this->resetDataArrays();
            $this->loadData();

        } catch (\Exception $e) {
            Log::error('Error in updatedSelectedMonth: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat mengubah bulan.');
        }
    }

    public function toggleDetails()
    {
        try {
            $this->showDetails = !$this->showDetails;
            $this->loadData();

        } catch (\Exception $e) {
            Log::error('Error in toggleDetails: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat mengubah tampilan detail.');
        }
    }

    public function loadData()
    {
        try {
            if (!Auth::check()) {
                $this->resetToDefaults();
                // Dispatch empty data if not authenticated
                $this->dispatch('update-chart', chartData: $this->getEmptyChartData(), hasValidChartData: false);
                return;
            }

            // Always load yearly data first
            $this->loadYearlyData();

            // Load monthly data based on conditions
            if (($this->reportType === 'yearly' && $this->selectedYear && $this->showDetails) ||
                ($this->reportType === 'monthly' && $this->selectedYear)
            ) {
                $this->loadMonthlyData();
            }
            
            // Load daily data if specific month is selected
            if ($this->reportType === 'monthly' && $this->selectedYear && $this->selectedMonth) {
                $this->loadDailyData();
            }

            // Always dispatch the latest chart data
            $this->dispatch('update-chart', chartData: $this->chartData, hasValidChartData: $this->hasValidChartData);

        } catch (\Exception $e) {
            Log::error('Error in loadData: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat memuat data laporan.');
            $this->resetToDefaults();
            // Dispatch empty data on error
            $this->dispatch('update-chart', chartData: $this->getEmptyChartData(), hasValidChartData: false);
        }
    }

    public function loadYearlyData()
    {
        try {
            if (!Auth::check()) {
                $this->yearlyData = [];
                return;
            }

            $years = $this->getAvailableYears();
            $this->yearlyData = [];

            foreach ($years as $year) {
                $totalIncome = Income::where('user_id', Auth::id())
                    ->whereYear('tanggal', $year)
                    ->get()
                    ->sum(function ($income) {
                        return ((float) ($income->jumlah_terjual ?? 0)) * ((float) ($income->harga_satuan ?? 0));
                    });

                $totalExpenditure = (float) Expenditure::where('user_id', Auth::id())
                    ->whereYear('tanggal', $year)
                    ->sum('jumlah');

                $totalFixedCost = (float) FixedCost::where('user_id', Auth::id())
                    ->whereYear('tanggal', $year)
                    ->sum('nominal');

                $totalExpenditure += $totalFixedCost;

                $profit = $totalIncome - $totalExpenditure;
                $margin = $totalIncome > 0 ? ($profit / $totalIncome) * 100 : 0;

                $this->yearlyData[] = [
                    'year' => $year,
                    'pendapatan' => $totalIncome,
                    'pengeluaran' => $totalExpenditure,
                    'laba_rugi' => $profit,
                    'margin' => $margin,
                ];
            }

        } catch (\Exception $e) {
            $this->yearlyData = [];
            Log::error('Error loading yearly data: ' . $e->getMessage());
        }
    }

    public function loadMonthlyData()
    {
        try {
            if (!Auth::check() || !$this->selectedYear || !is_numeric($this->selectedYear)) {
                $this->monthlyData = [];
                return;
            }

            $this->monthlyData = [];
            $year = (int) $this->selectedYear;

            for ($month = 1; $month <= 12; $month++) {
                $monthIncome = Income::where('user_id', Auth::id())
                    ->whereMonth('tanggal', $month)
                    ->whereYear('tanggal', $year)
                    ->get()
                    ->sum(function ($income) {
                        return ((float) ($income->jumlah_terjual ?? 0)) * ((float) ($income->harga_satuan ?? 0));
                    });

                $monthExpenditure = (float) Expenditure::where('user_id', Auth::id())
                    ->whereMonth('tanggal', $month)
                    ->whereYear('tanggal', $year)
                    ->sum('jumlah');

                $monthFixedCost = (float) FixedCost::where('user_id', Auth::id())
                    ->whereMonth('tanggal', $month)
                    ->whereYear('tanggal', $year)
                    ->sum('nominal');

                $monthTotalExpenditure = $monthExpenditure + $monthFixedCost;

                $profit = $monthIncome - $monthTotalExpenditure;
                $margin = $monthIncome > 0 ? ($profit / $monthIncome) * 100 : 0;

                $this->monthlyData[] = [
                    'month' => $month,
                    'month_name' => $this->monthNames[$month],
                    'pendapatan' => $monthIncome,
                    'pengeluaran' => $monthTotalExpenditure,
                    'biaya_tetap' => $monthFixedCost,
                    'laba_rugi' => $profit,
                    'margin' => $margin,
                ];
            }

        } catch (\Exception $e) {
            $this->monthlyData = [];
            Log::error('Error loading monthly data: ' . $e->getMessage());
        }
    }
    
    public function loadDailyData()
    {
        try {
            $year = (int)$this->selectedYear;
            $month = (int)$this->selectedMonth;
            
            if (!$year || !$month) {
                $this->dailyData = [];
                return;
            }

            // Fetch all incomes for the month once
            $incomes = Income::where('user_id', Auth::id())
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->get()
                ->groupBy(fn($item) => Carbon::parse($item->tanggal)->day);

            // Fetch all expenditures for the month once
            $expenditures = Expenditure::where('user_id', Auth::id())
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->get()
                ->groupBy(fn($item) => Carbon::parse($item->tanggal)->day);

            $this->dailyData = [];
            $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $dayIncome = $incomes->get($day, collect())->sum(function ($income) {
                    return ($income->jumlah_terjual ?? 0) * ($income->harga_satuan ?? 0);
                });

                $dayExpenditure = $expenditures->get($day, collect())->sum('jumlah');

                $dayFixedCost = FixedCost::where('user_id', Auth::id())
                    ->whereYear('tanggal', $year)
                    ->whereMonth('tanggal', $month)
                    ->whereDay('tanggal', $day)
                    ->sum('nominal');

                $dayExpenditure += $dayFixedCost;

                if ($dayIncome > 0 || $dayExpenditure > 0) {
                    $profit = $dayIncome - $dayExpenditure;
                    $margin = $dayIncome > 0 ? ($profit / $dayIncome) * 100 : 0;

                    $this->dailyData[] = [
                        'day' => $day,
                        'pendapatan' => $dayIncome,
                        'pengeluaran' => $dayExpenditure,
                        'laba_rugi' => $profit,
                        'margin' => $margin,
                    ];
                }
            }
        } catch(\Exception $e) {
            $this->dailyData = [];
            Log::error('Error loading daily data: ' . $e->getMessage());
        }
    }

    // ===============================
    // ENHANCED CHART DATA GENERATION
    // ===============================

    public function getChartDataProperty()
    {
        try {
            if (!Auth::check()) {
                return $this->getEmptyChartData();
            }

            $chartData = null;

            // Generate chart based on current filters and selections
            switch ($this->reportType) {
                case 'all':
                    $chartData = $this->generateYearlyChart();
                    break;

                case 'yearly':
                    if (!empty($this->selectedYear) && is_numeric($this->selectedYear)) {
                        $chartData = $this->generateMonthlyChart((int)$this->selectedYear);
                    } else {
                        $chartData = $this->generateYearlyChart();
                    }
                    break;

                case 'monthly':
                    if (!empty($this->selectedYear) && is_numeric($this->selectedYear)) {
                        if (!empty($this->selectedMonth) && is_numeric($this->selectedMonth)) {
                            // Daily chart for specific month
                            $chartData = $this->generateDailyChart((int)$this->selectedYear, (int)$this->selectedMonth);
                        } else {
                            // Monthly chart for year
                            $chartData = $this->generateMonthlyChart((int)$this->selectedYear);
                        }
                    } else {
                        $chartData = $this->generateYearlyChart();
                    }
                    break;

                default:
                    $chartData = $this->generateYearlyChart();
                    break;
            }

            // Validate and sanitize
            if ($chartData && is_array($chartData)) {
                $chartData = $this->sanitizeChartData($chartData);
                
                if ($this->chartHasMeaningfulData($chartData)) {
                    return $chartData;
                }
            }

            return $this->getEmptyChartData();

        } catch (\Exception $e) {
            Log::error('Error generating chart data: ' . $e->getMessage());
            return $this->getEmptyChartData();
        }
    }

    private function chartHasMeaningfulData($chartData)
    {
        if (empty($chartData['labels']) || 
            empty($chartData['pendapatan']) || 
            empty($chartData['pengeluaran']) || 
            empty($chartData['profit'])) {
            return false;
        }

        $totalIncome = array_sum(array_map(fn($val) => (float)$val, $chartData['pendapatan']));
        $totalExpense = array_sum(array_map(fn($val) => (float)$val, $chartData['pengeluaran']));

        return ($totalIncome > 0 || $totalExpense > 0);
    }

    private function generateYearlyChart()
    {
        try {
            $availableYears = $this->getAvailableYears();

            if (empty($availableYears)) {
                return $this->getEmptyChartData();
            }

            sort($availableYears);

            $labels = [];
            $pendapatanData = [];
            $pengeluaranData = [];
            $profitData = [];
            $hasData = false;

            foreach ($availableYears as $year) {
                $yearIncome = Income::where('user_id', Auth::id())
                    ->whereYear('tanggal', $year)
                    ->get()
                    ->sum(function ($income) {
                        return ((float) ($income->jumlah_terjual ?? 0)) * ((float) ($income->harga_satuan ?? 0));
                    });

                $yearExpenditure = (float) Expenditure::where('user_id', Auth::id())
                    ->whereYear('tanggal', $year)
                    ->sum('jumlah');

                $profit = $yearIncome - $yearExpenditure;

                $labels[] = (string) $year;
                $pendapatanData[] = $yearIncome;
                $pengeluaranData[] = $yearExpenditure;
                $profitData[] = $profit;
                
                if ($yearIncome > 0 || $yearExpenditure > 0) {
                    $hasData = true;
                }
            }

            if (!$hasData) {
                return $this->getEmptyChartData();
            }

            return [
                'labels' => $labels,
                'pendapatan' => $pendapatanData,
                'pengeluaran' => $pengeluaranData,
                'profit' => $profitData,
                'type' => 'yearly'
            ];

        } catch (\Exception $e) {
            Log::error('Error generating yearly chart: ' . $e->getMessage());
            return $this->getEmptyChartData();
        }
    }

    private function generateMonthlyChart($year = null)
    {
        try {
            if ($year === null) {
                $year = !empty($this->selectedYear) && is_numeric($this->selectedYear)
                       ? (int) $this->selectedYear
                       : now()->year;
            }

            $labels = [];
            $pendapatanData = [];
            $pengeluaranData = [];
            $profitData = [];
            $hasData = false;

            for ($month = 1; $month <= 12; $month++) {
                $monthIncome = Income::where('user_id', Auth::id())
                    ->whereYear('tanggal', $year)
                    ->whereMonth('tanggal', $month)
                    ->get()
                    ->sum(function ($income) {
                        return ((float) ($income->jumlah_terjual ?? 0)) * ((float) ($income->harga_satuan ?? 0));
                    });

                $monthExpenditure = (float) Expenditure::where('user_id', Auth::id())
                    ->whereYear('tanggal', $year)
                    ->whereMonth('tanggal', $month)
                    ->sum('jumlah');

                $profit = $monthIncome - $monthExpenditure;

                $labels[] = substr($this->monthNames[$month], 0, 3);
                $pendapatanData[] = $monthIncome;
                $pengeluaranData[] = $monthExpenditure;
                $profitData[] = $profit;
                
                if ($monthIncome > 0 || $monthExpenditure > 0) {
                    $hasData = true;
                }
            }

            if (!$hasData) {
                return $this->getEmptyChartData();
            }

            return [
                'labels' => $labels,
                'pendapatan' => $pendapatanData,
                'pengeluaran' => $pengeluaranData,
                'profit' => $profitData,
                'type' => 'monthly',
                'year' => $year
            ];

        } catch (\Exception $e) {
            Log::error('Error generating monthly chart: ' . $e->getMessage());
            return $this->getEmptyChartData();
        }
    }

    private function generateDailyChart($year = null, $month = null)
    {
        try {
            if ($year === null || $month === null) {
                if (empty($this->selectedYear) || empty($this->selectedMonth) ||
                    !is_numeric($this->selectedYear) || !is_numeric($this->selectedMonth)) {
                    return $this->generateMonthlyChart();
                }
                $year = (int) $this->selectedYear;
                $month = (int) $this->selectedMonth;
            }

            $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;

            $labels = [];
            $pendapatanData = [];
            $pengeluaranData = [];
            $profitData = [];
            $hasData = false;

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $dayIncome = Income::where('user_id', Auth::id())
                    ->whereYear('tanggal', $year)
                    ->whereMonth('tanggal', $month)
                    ->whereDay('tanggal', $day)
                    ->get()
                    ->sum(function ($income) {
                        return ((float) ($income->jumlah_terjual ?? 0)) * ((float) ($income->harga_satuan ?? 0));
                    });

                $dayExpenditure = (float) Expenditure::where('user_id', Auth::id())
                    ->whereYear('tanggal', $year)
                    ->whereMonth('tanggal', $month)
                    ->whereDay('tanggal', $day)
                    ->sum('jumlah');

                $profit = $dayIncome - $dayExpenditure;

                $labels[] = (string) $day;
                $pendapatanData[] = $dayIncome;
                $pengeluaranData[] = $dayExpenditure;
                $profitData[] = $profit;
                
                if ($dayIncome > 0 || $dayExpenditure > 0) {
                    $hasData = true;
                }
            }

            if (!$hasData) {
                return $this->getEmptyChartData();
            }

            return [
                'labels' => $labels,
                'pendapatan' => $pendapatanData,
                'pengeluaran' => $pengeluaranData,
                'profit' => $profitData,
                'type' => 'daily',
                'year' => $year,
                'month' => $month
            ];

        } catch (\Exception $e) {
            Log::error('Error generating daily chart: ' . $e->getMessage());
            return $this->getEmptyChartData();
        }
    }

    private function sanitizeChartData($chartData)
    {
        if (!is_array($chartData)) {
            return $this->getEmptyChartData();
        }

        $requiredKeys = ['labels', 'pendapatan', 'pengeluaran', 'profit'];
        foreach ($requiredKeys as $key) {
            if (!isset($chartData[$key]) || !is_array($chartData[$key])) {
                $chartData[$key] = [];
            }
        }

        // Ensure all arrays have the same length
        $maxLength = max(
            count($chartData['labels']),
            count($chartData['pendapatan']),
            count($chartData['pengeluaran']),
            count($chartData['profit'])
        );

        // Pad arrays to same length
        while (count($chartData['labels']) < $maxLength) {
            $chartData['labels'][] = '';
        }
        while (count($chartData['pendapatan']) < $maxLength) {
            $chartData['pendapatan'][] = 0;
        }
        while (count($chartData['pengeluaran']) < $maxLength) {
            $chartData['pengeluaran'][] = 0;
        }
        while (count($chartData['profit']) < $maxLength) {
            $chartData['profit'][] = 0;
        }

        // Ensure all numeric values are properly formatted
        $chartData['pendapatan'] = array_map(fn($val) => (float) ($val ?? 0), $chartData['pendapatan']);
        $chartData['pengeluaran'] = array_map(fn($val) => (float) ($val ?? 0), $chartData['pengeluaran']);
        $chartData['profit'] = array_map(fn($val) => (float) ($val ?? 0), $chartData['profit']);

        return $chartData;
    }

    private function getEmptyChartData()
    {
        return [
            'labels' => [],
            'pendapatan' => [],
            'pengeluaran' => [],
            'profit' => [],
            'type' => 'empty'
        ];
    }

    public function getHasValidChartDataProperty()
    {
        try {
            $chartData = $this->chartData;

            if (empty($chartData) || !is_array($chartData)) {
                return false;
            }

            if (empty($chartData['labels']) || !is_array($chartData['labels'])) {
                return false;
            }

            return $this->chartHasMeaningfulData($chartData);

        } catch (\Exception $e) {
            Log::error('Error in hasValidChartData: ' . $e->getMessage());
            return false;
        }
    }

    // ===============================
    // COMPUTED PROPERTIES FOR TOTALS
    // ===============================

    public function getTotalPendapatanProperty()
    {
        try {
            if (!Auth::check()) {
                return 0.0;
            }

            $query = Income::where('user_id', Auth::id());

            switch ($this->reportType) {
                case 'yearly':
                    if (!empty($this->selectedYear) && is_numeric($this->selectedYear)) {
                        $query->whereYear('tanggal', (int)$this->selectedYear);
                    }
                    break;

                case 'monthly':
                    if (!empty($this->selectedYear) && is_numeric($this->selectedYear)) {
                        $query->whereYear('tanggal', (int)$this->selectedYear);

                        if (!empty($this->selectedMonth) && is_numeric($this->selectedMonth)) {
                            $query->whereMonth('tanggal', (int)$this->selectedMonth);
                        }
                    }
                    break;
            }

            $total = $query->get()->sum(function ($income) {
                return ((float) ($income->jumlah_terjual ?? 0)) * ((float) ($income->harga_satuan ?? 0));
            });

            return (float) $total;
        } catch (\Exception $e) {
            Log::error('Error calculating total pendapatan: ' . $e->getMessage());
            return 0.0;
        }
    }

    public function getTotalPengeluaranProperty()
    {
        try {
            if (!Auth::check()) {
                return 0.0;
            }

            $expenditureQuery = Expenditure::where('user_id', Auth::id());
            $fixedCostQuery = FixedCost::where('user_id', Auth::id());

            switch ($this->reportType) {
                case 'yearly':
                    if (!empty($this->selectedYear) && is_numeric($this->selectedYear)) {
                        $expenditureQuery->whereYear('tanggal', (int)$this->selectedYear);
                        $fixedCostQuery->whereYear('tanggal', (int)$this->selectedYear);
                    }
                    break;

                case 'monthly':
                    if (!empty($this->selectedYear) && is_numeric($this->selectedYear)) {
                        $expenditureQuery->whereYear('tanggal', (int)$this->selectedYear);
                        $fixedCostQuery->whereYear('tanggal', (int)$this->selectedYear);

                        if (!empty($this->selectedMonth) && is_numeric($this->selectedMonth)) {
                            $expenditureQuery->whereMonth('tanggal', (int)$this->selectedMonth);
                            $fixedCostQuery->whereMonth('tanggal', (int)$this->selectedMonth);
                        }
                    }
                    break;
            }

            $totalExpenditure = $expenditureQuery->sum('jumlah') ?? 0;
            $totalFixedCost = $fixedCostQuery->sum('nominal') ?? 0;

            return (float) ($totalExpenditure + $totalFixedCost);
        } catch (\Exception $e) {
            Log::error('Error calculating total pengeluaran: ' . $e->getMessage());
            return 0.0;
        }
    }

    public function getLabaRugiProperty()
    {
        return $this->totalPendapatan - $this->totalPengeluaran;
    }

    public function getMarginProfitProperty()
    {
        $totalPendapatan = $this->totalPendapatan;
        if ($totalPendapatan > 0) {
            return ($this->labaRugi / $totalPendapatan) * 100;
        }
        return 0.0;
    }

    public function getGrowthRateProperty()
    {
        try {
            if (!Auth::check() || empty($this->selectedYear) || !is_numeric($this->selectedYear)) {
                return null;
            }

            $currentYear = (int) $this->selectedYear;
            $previousYear = $currentYear - 1;

            // Coba ambil dari yearlyData agar konsisten dengan tampilan
            $currentIncome = null;
            $previousIncome = null;

            foreach ($this->yearlyData as $row) {
                $rowYear = isset($row['year']) ? (int) $row['year'] : null;
                if ($rowYear === $currentYear) {
                    $currentIncome = (float) ($row['pendapatan'] ?? 0);
                } elseif ($rowYear === $previousYear) {
                    $previousIncome = (float) ($row['pendapatan'] ?? 0);
                }
            }

            // Jika yearlyData belum tersedia (mis. saat awal), fallback ke query
            if ($currentIncome === null) {
                $currentIncome = Income::where('user_id', Auth::id())
                    ->whereYear('tanggal', $currentYear)
                    ->get()
                    ->sum(function ($income) {
                        $computed = ((float) ($income->jumlah_terjual ?? 0)) * ((float) ($income->harga_satuan ?? 0));
                        if ($computed <= 0 && isset($income->total)) {
                            return (float) $income->total;
                        }
                        return (float) $computed;
                    });
            }
            if ($previousIncome === null) {
                $previousIncome = Income::where('user_id', Auth::id())
                    ->whereYear('tanggal', $previousYear)
                    ->get()
                    ->sum(function ($income) {
                        $computed = ((float) ($income->jumlah_terjual ?? 0)) * ((float) ($income->harga_satuan ?? 0));
                        if ($computed <= 0 && isset($income->total)) {
                            return (float) $income->total;
                        }
                        return (float) $computed;
                    });
            }

            // Tangani kasus tepi agar selalu ada nilai yang ditampilkan
            if ($previousIncome > 0) {
                return (($currentIncome - $previousIncome) / $previousIncome) * 100.0;
            }

            // Jika tahun sebelumnya 0:
            // - Jika tahun sekarang juga 0: pertumbuhan 0%
            // - Jika tahun sekarang > 0: anggap pertumbuhan 100% (dari basis 0)
            if ($previousIncome == 0) {
                return $currentIncome > 0 ? 100.0 : 0.0;
            }

            return 0.0;
        } catch (\Exception $e) {
            Log::error('Error calculating growth rate: ' . $e->getMessage());
            return null;
        }
    }

    // ===============================
    // UTILITY METHODS
    // ===============================

    public function exportCSV()
    {
        try {
            if (!Auth::check()) {
                session()->flash('error', 'Anda harus login terlebih dahulu.');
                return;
            }

            $data = [];
            $fileName = '';
            $headers = [];

            if ($this->reportType === 'yearly') {
                $data = $this->yearlyData;
                $fileName = 'laporan_rugi_laba_tahunan.csv';
                $headers = ['Tahun', 'Pendapatan', 'Pengeluaran', 'Laba/Rugi', 'Margin (%)'];
            } elseif ($this->reportType === 'monthly' && !empty($this->selectedYear)) {
                $data = $this->monthlyData;
                $monthName = !empty($this->selectedMonth) ? $this->monthNames[(int)$this->selectedMonth] : 'Semua_Bulan';
                $fileName = "laporan_rugi_laba_{$monthName}_{$this->selectedYear}.csv";
                $headers = ['Bulan', 'Pendapatan', 'Pengeluaran', 'Laba/Rugi', 'Margin (%)'];
            } else {
                session()->flash('error', 'Silakan pilih jenis laporan dan periode yang valid.');
                return;
            }

            if (empty($data)) {
                session()->flash('error', 'Tidak ada data untuk di-export.');
                return;
            }

            $csvHeaders = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ];

            $callback = function () use ($data, $headers) {
                $file = fopen('php://output', 'w');

                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
                fputcsv($file, $headers);

                foreach ($data as $row) {
                    if ($this->reportType === 'yearly') {
                        fputcsv($file, [
                            $row['year'],
                            $row['pendapatan'],
                            $row['pengeluaran'],
                            $row['laba_rugi'],
                            $row['margin']
                        ]);
                    } else {
                        fputcsv($file, [
                            $row['month_name'],
                            $row['pendapatan'],
                            $row['pengeluaran'],
                            $row['laba_rugi'],
                            $row['margin']
                        ]);
                    }
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $csvHeaders);
        } catch (\Exception $e) {
            Log::error('Error in exportCSV: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat export data.');
        }
    }

    public function getAvailableYears()
    {
        try {
            if (!Auth::check()) {
                return [now()->year];
            }

            $incomeYears = Income::where('user_id', Auth::id())
                ->whereNotNull('tanggal')
                ->selectRaw('DISTINCT YEAR(tanggal) as year')
                ->pluck('year')
                ->toArray();

            $expenditureYears = Expenditure::where('user_id', Auth::id())
                ->whereNotNull('tanggal')
                ->selectRaw('DISTINCT YEAR(tanggal) as year')
                ->pluck('year')
                ->toArray();

            $allYears = array_unique(array_merge($incomeYears, $expenditureYears));
            rsort($allYears);

            return $allYears ?: [now()->year];
        } catch (\Exception $e) {
            Log::error('Error getting available years: ' . $e->getMessage());
            return [now()->year];
        }
    }

    public function getSelectedMonthNameProperty()
    {
        return isset($this->monthNames[(int) $this->selectedMonth])
            ? $this->monthNames[(int) $this->selectedMonth]
            : '';
    }

    public function getIsMonthSelectedProperty()
    {
        return $this->reportType === 'monthly' &&
            !empty($this->selectedYear) &&
            !empty($this->selectedMonth) &&
            is_numeric($this->selectedMonth) &&
            (int)$this->selectedMonth >= 1 &&
            (int)$this->selectedMonth <= 12;
    }

    public function render()
    {
        try {
            if (!Auth::check()) {
                session()->flash('error', 'Anda harus login terlebih dahulu.');
                return $this->getDefaultViewData();
            }

            return view('livewire.reports.profit-loss', [
                'totalPendapatan' => $this->totalPendapatan,
                'totalPengeluaran' => $this->totalPengeluaran,
                'labaRugi' => $this->labaRugi,
                'marginProfit' => $this->marginProfit,
                'growthRate' => $this->growthRate,
                'yearlyData' => $this->yearlyData,
                'monthlyData' => $this->monthlyData,
                'dailyData' => $this->dailyData,
                'chartData' => $this->chartData,
                'hasValidChartData' => $this->hasValidChartData,
                'availableYears' => $this->getAvailableYears(),
                'monthNames' => $this->monthNames,
                'isMonthSelected' => $this->isMonthSelected,
                'selectedMonthName' => $this->selectedMonthName,
                'reportType' => $this->reportType,
                'selectedYear' => $this->selectedYear,
                'selectedMonth' => $this->selectedMonth,
                'showDetails' => $this->showDetails,
            ]);

        } catch (\Exception $e) {
            Log::error('CRITICAL ERROR in render: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan sistem. Silakan refresh halaman.');
            return $this->getDefaultViewData();
        }
    }

    private function getDefaultViewData()
    {
        return view('livewire.reports.profit-loss', [
            'totalPendapatan' => 0.0,
            'totalPengeluaran' => 0.0,
            'labaRugi' => 0.0,
            'marginProfit' => 0.0,
            'growthRate' => null,
            'yearlyData' => [],
            'monthlyData' => [],
            'dailyData' => [],
            'chartData' => $this->getEmptyChartData(),
            'hasValidChartData' => false,
            'availableYears' => [now()->year],
            'monthNames' => $this->monthNames,
            'isMonthSelected' => false,
            'selectedMonthName' => '',
            'reportType' => $this->reportType,
            'selectedYear' => $this->selectedYear,
            'selectedMonth' => $this->selectedMonth,
            'showDetails' => $this->showDetails,
        ]);
    }
}