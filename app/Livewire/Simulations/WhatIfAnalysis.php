<?php

namespace App\Livewire\Simulations;

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\Income;
use App\Models\Expenditure;
use App\Models\FixedCost;
use App\Models\Capital;
use App\Models\Capitalearly;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WhatIfAnalysis extends Component
{
    #[Title('Analisis What If')]

    // Filter periode
    public $selectedYear;
    public $selectedMonth;
    
    // Data aktual
    public $actualData = [];
    
    // Skenario What If
    public $scenarios = [];
    public $selectedScenario = '';
    
    // Input untuk skenario baru
    public $scenarioName = '';
    public $scenarioDescription = '';
    
    // Parameter perubahan
    public $priceChangePercent = 0;
    public $volumeChangePercent = 0;
    public $costChangePercent = 0;
    public $fixedCostChangePercent = 0;
    
    // Hasil analisis
    public $analysisResults = [];
    public $comparisonTable = [];
    
    // Chart data
    public $chartData = [];
    
    // Array nama bulan
    public array $monthNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];

    public function mount()
    {
        $this->selectedYear = now()->year;
        $this->selectedMonth = now()->month;
        $this->loadActualData();
        $this->initializeScenarios();
    }

    public function updatedSelectedYear()
    {
        $this->loadActualData();
        $this->runAnalysis();
    }

    public function updatedSelectedMonth()
    {
        $this->loadActualData();
        $this->runAnalysis();
    }

    public function updatedPriceChangePercent()
    {
        $this->runAnalysis();
    }

    public function updatedVolumeChangePercent()
    {
        $this->runAnalysis();
    }

    public function updatedCostChangePercent()
    {
        $this->runAnalysis();
    }

    public function updatedFixedCostChangePercent()
    {
        $this->runAnalysis();
    }

    public function updatedSelectedScenario()
    {
        $this->loadScenarioData();
        $this->runAnalysis();
    }

    /**
     * Load data aktual dari database
     */
    public function loadActualData()
    {
        try {
            $year = (int) $this->selectedYear;
            $month = (int) $this->selectedMonth;

            // Data pendapatan
            $incomes = Income::where('user_id', Auth::id())
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->get();

            $totalRevenue = $incomes->sum(function ($income) {
                return ($income->jumlah_terjual ?? 0) * ($income->harga_satuan ?? 0);
            });

            $totalUnits = $incomes->sum('jumlah_terjual');
            $avgPrice = $totalUnits > 0 ? $totalRevenue / $totalUnits : 0;

            // Data pengeluaran variabel
            $totalVariableCost = Expenditure::where('user_id', Auth::id())
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->sum('jumlah');

            // Data biaya tetap
            $totalFixedCost = FixedCost::where('user_id', Auth::id())
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->sum('nominal');

            // Data modal
            $totalCapital = Capital::where('user_id', Auth::id())
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->where('jenis', 'keluar')
                ->sum('nominal');

            $this->actualData = [
                'revenue' => (float) $totalRevenue,
                'units' => (int) $totalUnits,
                'avg_price' => (float) $avgPrice,
                'variable_cost' => (float) $totalVariableCost,
                'fixed_cost' => (float) $totalFixedCost,
                'capital_outflow' => (float) $totalCapital,
                'total_cost' => (float) ($totalVariableCost + $totalFixedCost + $totalCapital),
                'profit' => (float) ($totalRevenue - $totalVariableCost - $totalFixedCost - $totalCapital),
                'profit_margin' => $totalRevenue > 0 ? 
                    (float) (($totalRevenue - $totalVariableCost - $totalFixedCost - $totalCapital) / $totalRevenue * 100) : 0.0
            ];

        } catch (\Exception $e) {
            $this->actualData = [];
            session()->flash('error', 'Error loading data: ' . $e->getMessage());
        }
    }

    /**
     * Inisialisasi skenario default
     */
    public function initializeScenarios()
    {
        $this->scenarios = [
            'baseline' => [
                'name' => 'Skenario Baseline',
                'description' => 'Kondisi aktual saat ini',
                'price_change' => 0,
                'volume_change' => 0,
                'cost_change' => 0,
                'fixed_cost_change' => 0
            ],
            'optimistic' => [
                'name' => 'Skenario Optimis',
                'description' => 'Harga naik 10%, volume naik 20%, biaya turun 5%',
                'price_change' => 10,
                'volume_change' => 20,
                'cost_change' => -5,
                'fixed_cost_change' => 0
            ],
            'pessimistic' => [
                'name' => 'Skenario Pesimis',
                'description' => 'Harga turun 5%, volume turun 15%, biaya naik 10%',
                'price_change' => -5,
                'volume_change' => -15,
                'cost_change' => 10,
                'fixed_cost_change' => 5
            ],
            'cost_optimization' => [
                'name' => 'Optimasi Biaya',
                'description' => 'Fokus pada pengurangan biaya variabel dan tetap',
                'price_change' => 0,
                'volume_change' => 0,
                'cost_change' => -15,
                'fixed_cost_change' => -10
            ],
            'price_volume' => [
                'name' => 'Strategi Harga & Volume',
                'description' => 'Harga naik 5%, volume naik 25%',
                'price_change' => 5,
                'volume_change' => 25,
                'cost_change' => 0,
                'fixed_cost_change' => 0
            ]
        ];

        $this->selectedScenario = 'baseline';
    }

    /**
     * Load data skenario yang dipilih
     */
    public function loadScenarioData()
    {
        if (isset($this->scenarios[$this->selectedScenario])) {
            $scenario = $this->scenarios[$this->selectedScenario];
            $this->priceChangePercent = $scenario['price_change'];
            $this->volumeChangePercent = $scenario['volume_change'];
            $this->costChangePercent = $scenario['cost_change'];
            $this->fixedCostChangePercent = $scenario['fixed_cost_change'];
        }
    }

    /**
     * Jalankan analisis What If
     */
    public function runAnalysis()
    {
        if (empty($this->actualData)) {
            return;
        }

        try {
            $this->analysisResults = [];
            $this->comparisonTable = [];

            // Skenario Baseline (aktual)
            $baseline = $this->calculateScenario('Baseline', 0, 0, 0, 0);
            $this->analysisResults['baseline'] = $baseline;

            // Skenario What If
            $whatIf = $this->calculateScenario(
                'What If', 
                $this->priceChangePercent, 
                $this->volumeChangePercent, 
                $this->costChangePercent, 
                $this->fixedCostChangePercent
            );
            $this->analysisResults['what_if'] = $whatIf;

            // Skenario ekstrem untuk chart
            $this->generateChartData();

            // Buat tabel perbandingan
            $this->createComparisonTable();

        } catch (\Exception $e) {
            session()->flash('error', 'Error in analysis: ' . $e->getMessage());
        }
    }

    /**
     * Hitung skenario berdasarkan parameter perubahan
     */
    private function calculateScenario($name, $priceChange, $volumeChange, $costChange, $fixedCostChange)
    {
        $actual = $this->actualData;

        // Type casting dan validasi
        $priceChange = (float) $priceChange;
        $volumeChange = (float) $volumeChange;
        $costChange = (float) $costChange;
        $fixedCostChange = (float) $fixedCostChange;

        // Perhitungan perubahan dengan validasi
        $newPrice = (float) $actual['avg_price'] * (1 + $priceChange / 100);
        $newVolume = (float) $actual['units'] * (1 + $volumeChange / 100);
        $newRevenue = $newPrice * $newVolume;
        
        $newVariableCost = (float) $actual['variable_cost'] * (1 + $costChange / 100);
        $newFixedCost = (float) $actual['fixed_cost'] * (1 + $fixedCostChange / 100);
        
        $newTotalCost = $newVariableCost + $newFixedCost + (float) $actual['capital_outflow'];
        $newProfit = $newRevenue - $newTotalCost;
        $newProfitMargin = $newRevenue > 0 ? ($newProfit / $newRevenue) * 100 : 0;

        // Perubahan absolut dan persentase
        $revenueChange = $newRevenue - (float) $actual['revenue'];
        $revenueChangePercent = (float) $actual['revenue'] > 0 ? ($revenueChange / (float) $actual['revenue']) * 100 : 0;
        
        $profitChange = $newProfit - (float) $actual['profit'];
        $profitChangePercent = (float) $actual['profit'] != 0 ? ($profitChange / abs((float) $actual['profit'])) * 100 : 0;

        // Break-even analysis dengan validasi
        $contributionMargin = $newRevenue - $newVariableCost;
        $contributionMarginRatio = $newRevenue > 0 ? $contributionMargin / $newRevenue : 0;
        
        // Validasi untuk mencegah division by zero
        $breakEvenRevenue = $contributionMarginRatio > 0 ? $newFixedCost / $contributionMarginRatio : 0;
        $breakEvenUnits = ($contributionMarginRatio > 0 && $newPrice > 0) ? $breakEvenRevenue / $newPrice : 0;

        // Margin of safety dengan validasi
        $marginOfSafety = ($newRevenue > 0 && $breakEvenRevenue > 0) ? (($newRevenue - $breakEvenRevenue) / $newRevenue) * 100 : 0;

        return [
            'name' => $name,
            'revenue' => $newRevenue,
            'units' => $newVolume,
            'avg_price' => $newPrice,
            'variable_cost' => $newVariableCost,
            'fixed_cost' => $newFixedCost,
            'total_cost' => $newTotalCost,
            'profit' => $newProfit,
            'profit_margin' => $newProfitMargin,
            'revenue_change' => $revenueChange,
            'revenue_change_percent' => $revenueChangePercent,
            'profit_change' => $profitChange,
            'profit_change_percent' => $profitChangePercent,
            'contribution_margin' => $contributionMargin,
            'contribution_margin_ratio' => $contributionMarginRatio,
            'break_even_revenue' => $breakEvenRevenue,
            'break_even_units' => $breakEvenUnits,
            'margin_of_safety' => $marginOfSafety
        ];
    }

    /**
     * Generate data untuk chart
     */
    private function generateChartData()
    {
        $this->chartData = [];

        // Revenue comparison
        $this->chartData['revenue'] = [
            'labels' => ['Aktual', 'What If'],
            'datasets' => [
                [
                    'label' => 'Pendapatan',
                    'data' => [
                        (float) $this->actualData['revenue'],
                        (float) ($this->analysisResults['what_if']['revenue'] ?? 0)
                    ],
                    'backgroundColor' => ['#28a745', '#007bff'],
                    'borderColor' => ['#28a745', '#007bff'],
                    'borderWidth' => 2
                ]
            ]
        ];

        // Profit comparison
        $this->chartData['profit'] = [
            'labels' => ['Aktual', 'What If'],
            'datasets' => [
                [
                    'label' => 'Laba',
                    'data' => [
                        (float) $this->actualData['profit'],
                        (float) ($this->analysisResults['what_if']['profit'] ?? 0)
                    ],
                    'backgroundColor' => ['#ffc107', '#dc3545'],
                    'borderColor' => ['#ffc107', '#dc3545'],
                    'borderWidth' => 2
                ]
            ]
        ];

        // Cost breakdown
        $this->chartData['costs'] = [
            'labels' => ['Biaya Variabel', 'Biaya Tetap', 'Modal Keluar'],
            'datasets' => [
                [
                    'label' => 'Aktual',
                    'data' => [
                        (float) $this->actualData['variable_cost'],
                        (float) $this->actualData['fixed_cost'],
                        (float) $this->actualData['capital_outflow']
                    ],
                    'backgroundColor' => ['#dc3545', '#fd7e14', '#6f42c1']
                ],
                [
                    'label' => 'What If',
                    'data' => [
                        (float) ($this->analysisResults['what_if']['variable_cost'] ?? 0),
                        (float) ($this->analysisResults['what_if']['fixed_cost'] ?? 0),
                        (float) $this->actualData['capital_outflow']
                    ],
                    'backgroundColor' => ['#e83e8c', '#fd7e14', '#6f42c1']
                ]
            ]
        ];
    }

    /**
     * Buat tabel perbandingan
     */
    private function createComparisonTable()
    {
        $this->comparisonTable = [
            'metrics' => [
                'Pendapatan' => [
                    'aktual' => (float) $this->actualData['revenue'],
                    'what_if' => (float) ($this->analysisResults['what_if']['revenue'] ?? 0),
                    'change' => (float) ($this->analysisResults['what_if']['revenue_change'] ?? 0),
                    'change_percent' => (float) ($this->analysisResults['what_if']['revenue_change_percent'] ?? 0)
                ],
                'Jumlah Unit' => [
                    'aktual' => (int) $this->actualData['units'],
                    'what_if' => (int) ($this->analysisResults['what_if']['units'] ?? 0),
                    'change' => (int) (($this->analysisResults['what_if']['units'] ?? 0) - $this->actualData['units']),
                    'change_percent' => $this->actualData['units'] > 0 ? 
                        (float) ((($this->analysisResults['what_if']['units'] ?? 0) - $this->actualData['units']) / $this->actualData['units'] * 100) : 0.0
                ],
                'Harga Rata-rata' => [
                    'aktual' => (float) $this->actualData['avg_price'],
                    'what_if' => (float) ($this->analysisResults['what_if']['avg_price'] ?? 0),
                    'change' => (float) (($this->analysisResults['what_if']['avg_price'] ?? 0) - $this->actualData['avg_price']),
                    'change_percent' => $this->actualData['avg_price'] > 0 ? 
                        (float) ((($this->analysisResults['what_if']['avg_price'] ?? 0) - $this->actualData['avg_price']) / $this->actualData['avg_price'] * 100) : 0.0
                ],
                'Biaya Variabel' => [
                    'aktual' => (float) $this->actualData['variable_cost'],
                    'what_if' => (float) ($this->analysisResults['what_if']['variable_cost'] ?? 0),
                    'change' => (float) (($this->analysisResults['what_if']['variable_cost'] ?? 0) - $this->actualData['variable_cost']),
                    'change_percent' => $this->actualData['variable_cost'] > 0 ? 
                        (float) ((($this->analysisResults['what_if']['variable_cost'] ?? 0) - $this->actualData['variable_cost']) / $this->actualData['variable_cost'] * 100) : 0.0
                ],
                'Biaya Tetap' => [
                    'aktual' => (float) $this->actualData['fixed_cost'],
                    'what_if' => (float) ($this->analysisResults['what_if']['fixed_cost'] ?? 0),
                    'change' => (float) (($this->analysisResults['what_if']['fixed_cost'] ?? 0) - $this->actualData['fixed_cost']),
                    'change_percent' => $this->actualData['fixed_cost'] > 0 ? 
                        (float) ((($this->analysisResults['what_if']['fixed_cost'] ?? 0) - $this->actualData['fixed_cost']) / $this->actualData['fixed_cost'] * 100) : 0.0
                ],
                'Total Biaya' => [
                    'aktual' => (float) $this->actualData['total_cost'],
                    'what_if' => (float) ($this->analysisResults['what_if']['total_cost'] ?? 0),
                    'change' => (float) (($this->analysisResults['what_if']['total_cost'] ?? 0) - $this->actualData['total_cost']),
                    'change_percent' => $this->actualData['total_cost'] > 0 ? 
                        (float) ((($this->analysisResults['what_if']['total_cost'] ?? 0) - $this->actualData['total_cost']) / $this->actualData['total_cost'] * 100) : 0.0
                ],
                'Laba' => [
                    'aktual' => (float) $this->actualData['profit'],
                    'what_if' => (float) ($this->analysisResults['what_if']['profit'] ?? 0),
                    'change' => (float) ($this->analysisResults['what_if']['profit_change'] ?? 0),
                    'change_percent' => (float) ($this->analysisResults['what_if']['profit_change_percent'] ?? 0)
                ],
                'Margin Laba' => [
                    'aktual' => (float) $this->actualData['profit_margin'],
                    'what_if' => (float) ($this->analysisResults['what_if']['profit_margin'] ?? 0),
                    'change' => (float) (($this->analysisResults['what_if']['profit_margin'] ?? 0) - $this->actualData['profit_margin']),
                    'change_percent' => $this->actualData['profit_margin'] != 0 ? 
                        (float) ((($this->analysisResults['what_if']['profit_margin'] ?? 0) - $this->actualData['profit_margin']) / abs($this->actualData['profit_margin']) * 100) : 0.0
                ]
            ]
        ];
    }

    /**
     * Buat skenario custom
     */
    public function createCustomScenario()
    {
        $this->validate([
            'scenarioName' => 'required|string|max:100',
            'scenarioDescription' => 'required|string|max:500'
        ]);

        $scenarioKey = 'custom_' . time();
        $this->scenarios[$scenarioKey] = [
            'name' => $this->scenarioName,
            'description' => $this->scenarioDescription,
            'price_change' => $this->priceChangePercent,
            'volume_change' => $this->volumeChangePercent,
            'cost_change' => $this->costChangePercent,
            'fixed_cost_change' => $this->fixedCostChangePercent
        ];

        $this->selectedScenario = $scenarioKey;
        $this->scenarioName = '';
        $this->scenarioDescription = '';
        
        session()->flash('message', 'Skenario custom berhasil dibuat!');
    }

    /**
     * Reset parameter ke default
     */
    public function resetParameters()
    {
        $this->priceChangePercent = 0;
        $this->volumeChangePercent = 0;
        $this->costChangePercent = 0;
        $this->fixedCostChangePercent = 0;
        $this->runAnalysis();
    }

    public function render()
    {
        return view('livewire.simulations.what-if-analysis');
    }
}
