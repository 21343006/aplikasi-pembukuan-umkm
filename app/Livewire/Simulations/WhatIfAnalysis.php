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
    
    // Analisis mendalam untuk UMKM
    public $businessInsights = [];
    public $recommendations = [];
    public $riskAssessment = [];
    public $actionPlan = [];
    
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
            $incomes = Income::
                whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->get();

            $totalRevenue = $incomes->sum(function ($income) {
                return ($income->jumlah_terjual ?? 0) * ($income->harga_satuan ?? 0);
            });

            $totalUnits = $incomes->sum('jumlah_terjual');
            $avgPrice = $totalUnits > 0 ? $totalRevenue / $totalUnits : 0;

            // Data pengeluaran variabel
            $totalVariableCost = Expenditure::
                whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->sum('jumlah');

            // Data biaya tetap
            $totalFixedCost = FixedCost::
                whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->sum('nominal');

            // Data modal
            $totalCapital = Capital::
                whereYear('tanggal', $year)
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
                'description' => 'Kondisi aktual saat ini tanpa perubahan',
                'price_change' => 0,
                'volume_change' => 0,
                'cost_change' => 0,
                'fixed_cost_change' => 0
            ],
            'optimistic' => [
                'name' => 'Skenario Optimis',
                'description' => 'Kondisi terbaik yang bisa terjadi - harga naik, volume naik, biaya turun',
                'price_change' => 15,
                'volume_change' => 30,
                'cost_change' => -10,
                'fixed_cost_change' => -5
            ],
            'realistic' => [
                'name' => 'Skenario Realistis',
                'description' => 'Kondisi yang paling mungkin terjadi - perubahan moderat',
                'price_change' => 5,
                'volume_change' => 10,
                'cost_change' => 0,
                'fixed_cost_change' => 0
            ],
            'pessimistic' => [
                'name' => 'Skenario Pesimis',
                'description' => 'Kondisi terburuk yang bisa terjadi - harga turun, volume turun, biaya naik',
                'price_change' => -10,
                'volume_change' => -20,
                'cost_change' => 15,
                'fixed_cost_change' => 10
            ],
            'cost_optimization' => [
                'name' => 'Strategi Optimasi Biaya',
                'description' => 'Fokus pada pengurangan biaya operasional untuk meningkatkan margin',
                'price_change' => 0,
                'volume_change' => 0,
                'cost_change' => -20,
                'fixed_cost_change' => -15
            ],
            'growth_strategy' => [
                'name' => 'Strategi Pertumbuhan',
                'description' => 'Fokus pada peningkatan volume penjualan dan harga',
                'price_change' => 8,
                'volume_change' => 35,
                'cost_change' => 5,
                'fixed_cost_change' => 0
            ],
            'market_expansion' => [
                'name' => 'Ekspansi Pasar',
                'description' => 'Strategi untuk masuk ke pasar baru dengan investasi tambahan',
                'price_change' => 0,
                'volume_change' => 50,
                'cost_change' => 10,
                'fixed_cost_change' => 25
            ],
            'crisis_management' => [
                'name' => 'Manajemen Krisis',
                'description' => 'Strategi bertahan saat kondisi ekonomi sulit',
                'price_change' => -5,
                'volume_change' => -30,
                'cost_change' => -15,
                'fixed_cost_change' => -20
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


            // Buat tabel perbandingan
            $this->createComparisonTable();
            
            // Generate analisis mendalam untuk UMKM
            $this->generateBusinessInsights();
            $this->generateRecommendations();
            $this->assessRisk();
            $this->createActionPlan();

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
     * Generate business insights untuk UMKM
     */
    private function generateBusinessInsights()
    {
        if (empty($this->analysisResults['what_if'])) {
            return;
        }

        $whatIf = $this->analysisResults['what_if'];
        $actual = $this->actualData;

        $this->businessInsights = [
            'profitability' => [
                'title' => 'Analisis Profitabilitas',
                'description' => $this->getProfitabilityInsight($whatIf, $actual),
                'level' => $this->getProfitabilityLevel($whatIf['profit_margin']),
                'icon' => $this->getProfitabilityIcon($whatIf['profit_margin'])
            ],
            'efficiency' => [
                'title' => 'Efisiensi Operasional',
                'description' => $this->getEfficiencyInsight($whatIf, $actual),
                'level' => $this->getEfficiencyLevel($whatIf),
                'icon' => 'bi-speedometer2'
            ],
            'growth' => [
                'title' => 'Potensi Pertumbuhan',
                'description' => $this->getGrowthInsight($whatIf, $actual),
                'level' => $this->getGrowthLevel($whatIf['revenue_change_percent']),
                'icon' => 'bi-graph-up-arrow'
            ],
            'sustainability' => [
                'title' => 'Keberlanjutan Bisnis',
                'description' => $this->getSustainabilityInsight($whatIf, $actual),
                'level' => $this->getSustainabilityLevel($whatIf),
                'icon' => 'bi-shield-check'
            ]
        ];
    }

    /**
     * Generate recommendations berdasarkan analisis
     */
    private function generateRecommendations()
    {
        if (empty($this->analysisResults['what_if'])) {
            return;
        }

        $whatIf = $this->analysisResults['what_if'];
        $actual = $this->actualData;

        $this->recommendations = [];

        // Rekomendasi berdasarkan profit margin
        if ($whatIf['profit_margin'] < 10) {
            $this->recommendations[] = [
                'type' => 'warning',
                'title' => 'Margin Laba Rendah',
                'description' => 'Margin laba di bawah 10% menunjukkan risiko tinggi. Pertimbangkan untuk:',
                'actions' => [
                    'Meningkatkan harga jual dengan value proposition yang lebih baik',
                    'Mengurangi biaya operasional melalui efisiensi',
                    'Mencari supplier dengan harga lebih kompetitif',
                    'Diversifikasi produk dengan margin lebih tinggi'
                ],
                'priority' => 'high'
            ];
        }

        // Rekomendasi berdasarkan break-even
        if ($whatIf['break_even_units'] > $whatIf['units']) {
            $this->recommendations[] = [
                'type' => 'danger',
                'title' => 'Belum Mencapai Break-Even',
                'description' => 'Penjualan saat ini belum mencapai titik impas. Strategi yang bisa dilakukan:',
                'actions' => [
                    'Meningkatkan volume penjualan melalui marketing',
                    'Menurunkan biaya tetap dengan negosiasi kontrak',
                    'Meningkatkan harga jual secara bertahap',
                    'Mencari peluang kerjasama untuk mengurangi biaya'
                ],
                'priority' => 'critical'
            ];
        }

        // Rekomendasi berdasarkan margin of safety
        if ($whatIf['margin_of_safety'] < 20) {
            $this->recommendations[] = [
                'type' => 'info',
                'title' => 'Margin of Safety Rendah',
                'description' => 'Margin of safety di bawah 20% menunjukkan sensitivitas tinggi terhadap perubahan. Saran:',
                'actions' => [
                    'Membangun cash reserve untuk menghadapi fluktuasi',
                    'Diversifikasi sumber pendapatan',
                    'Mengembangkan produk dengan permintaan stabil',
                    'Membuat rencana kontinjensi untuk situasi sulit'
                ],
                'priority' => 'medium'
            ];
        }

        // Rekomendasi positif
        if ($whatIf['profit_margin'] > 20 && $whatIf['margin_of_safety'] > 30) {
            $this->recommendations[] = [
                'type' => 'success',
                'title' => 'Kondisi Bisnis Sehat',
                'description' => 'Bisnis dalam kondisi yang baik dengan margin dan keamanan yang memadai. Peluang:',
                'actions' => [
                    'Pertimbangkan ekspansi bisnis',
                    'Investasi dalam teknologi untuk efisiensi',
                    'Mengembangkan produk baru',
                    'Meningkatkan kapasitas produksi'
                ],
                'priority' => 'low'
            ];
        }
    }

    /**
     * Assess business risk
     */
    private function assessRisk()
    {
        if (empty($this->analysisResults['what_if'])) {
            return;
        }

        $whatIf = $this->analysisResults['what_if'];
        $actual = $this->actualData;

        $riskFactors = [];
        $riskScore = 0;

        // Risk factor: Low profit margin
        if ($whatIf['profit_margin'] < 10) {
            $riskFactors[] = [
                'factor' => 'Margin Laba Rendah',
                'impact' => 'Risiko tinggi terhadap perubahan biaya',
                'mitigation' => 'Tingkatkan efisiensi dan harga jual'
            ];
            $riskScore += 3;
        }

        // Risk factor: High break-even point
        if ($whatIf['break_even_units'] > $whatIf['units'] * 1.2) {
            $riskFactors[] = [
                'factor' => 'Break-Even Point Tinggi',
                'impact' => 'Sulit mencapai profitabilitas',
                'mitigation' => 'Turunkan biaya tetap dan variabel'
            ];
            $riskScore += 2;
        }

        // Risk factor: Low margin of safety
        if ($whatIf['margin_of_safety'] < 15) {
            $riskFactors[] = [
                'factor' => 'Margin of Safety Rendah',
                'impact' => 'Sensitif terhadap penurunan penjualan',
                'mitigation' => 'Bangun cash reserve dan diversifikasi'
            ];
            $riskScore += 2;
        }

        // Risk factor: High cost ratio
        $costRatio = ($whatIf['total_cost'] / $whatIf['revenue']) * 100;
        if ($costRatio > 90) {
            $riskFactors[] = [
                'factor' => 'Rasio Biaya Tinggi',
                'impact' => 'Sedikit ruang untuk margin',
                'mitigation' => 'Optimasi biaya operasional'
            ];
            $riskScore += 2;
        }

        $this->riskAssessment = [
            'score' => $riskScore,
            'level' => $this->getRiskLevel($riskScore),
            'factors' => $riskFactors,
            'overall_assessment' => $this->getOverallRiskAssessment($riskScore)
        ];
    }

    /**
     * Create action plan
     */
    private function createActionPlan()
    {
        if (empty($this->analysisResults['what_if'])) {
            return;
        }

        $whatIf = $this->analysisResults['what_if'];
        $actual = $this->actualData;

        $this->actionPlan = [
            'immediate' => $this->getImmediateActions($whatIf, $actual),
            'short_term' => $this->getShortTermActions($whatIf, $actual),
            'long_term' => $this->getLongTermActions($whatIf, $actual)
        ];
    }

    // Helper methods untuk insights
    private function getProfitabilityInsight($whatIf, $actual)
    {
        $margin = $whatIf['profit_margin'];
        if ($margin > 20) {
            return "Margin laba {$margin}% sangat baik! Bisnis Anda sangat profitable dan memiliki ruang untuk investasi atau ekspansi.";
        } elseif ($margin > 10) {
            return "Margin laba {$margin}% cukup baik. Bisnis Anda profitable dengan potensi untuk ditingkatkan lebih lanjut.";
        } elseif ($margin > 0) {
            return "Margin laba {$margin}% masih positif tapi rendah. Perlu strategi untuk meningkatkan profitabilitas.";
        } else {
            return "Margin laba negatif {$margin}%. Bisnis mengalami kerugian dan memerlukan tindakan segera.";
        }
    }

    private function getEfficiencyInsight($whatIf, $actual)
    {
        $costRatio = ($whatIf['total_cost'] / $whatIf['revenue']) * 100;
        if ($costRatio < 70) {
            return "Rasio biaya {$costRatio}% sangat efisien! Operasional bisnis berjalan dengan baik.";
        } elseif ($costRatio < 85) {
            return "Rasio biaya {$costRatio}% cukup efisien. Ada ruang untuk optimasi lebih lanjut.";
        } else {
            return "Rasio biaya {$costRatio}% tinggi. Perlu fokus pada efisiensi operasional.";
        }
    }

    private function getGrowthInsight($whatIf, $actual)
    {
        $revenueChange = $whatIf['revenue_change_percent'];
        if ($revenueChange > 20) {
            return "Pertumbuhan pendapatan {$revenueChange}% sangat tinggi! Bisnis menunjukkan momentum yang kuat.";
        } elseif ($revenueChange > 5) {
            return "Pertumbuhan pendapatan {$revenueChange}% positif. Bisnis berkembang dengan baik.";
        } elseif ($revenueChange > -5) {
            return "Pertumbuhan pendapatan {$revenueChange}% stabil. Perlu strategi untuk akselerasi.";
        } else {
            return "Pertumbuhan pendapatan {$revenueChange}% menurun. Perlu evaluasi dan perbaikan strategi.";
        }
    }

    private function getSustainabilityInsight($whatIf, $actual)
    {
        $marginOfSafety = $whatIf['margin_of_safety'];
        if ($marginOfSafety > 40) {
            return "Margin of safety {$marginOfSafety}% sangat aman. Bisnis tahan terhadap fluktuasi pasar.";
        } elseif ($marginOfSafety > 20) {
            return "Margin of safety {$marginOfSafety}% cukup aman. Bisnis memiliki buffer yang memadai.";
        } else {
            return "Margin of safety {$marginOfSafety}% rendah. Bisnis sensitif terhadap perubahan penjualan.";
        }
    }

    // Helper methods untuk level assessment
    private function getProfitabilityLevel($margin)
    {
        if ($margin > 20) return 'excellent';
        if ($margin > 10) return 'good';
        if ($margin > 0) return 'fair';
        return 'poor';
    }

    private function getEfficiencyLevel($whatIf)
    {
        $costRatio = ($whatIf['total_cost'] / $whatIf['revenue']) * 100;
        if ($costRatio < 70) return 'excellent';
        if ($costRatio < 85) return 'good';
        return 'needs_improvement';
    }

    private function getGrowthLevel($revenueChange)
    {
        if ($revenueChange > 20) return 'excellent';
        if ($revenueChange > 5) return 'good';
        if ($revenueChange > -5) return 'stable';
        return 'declining';
    }

    private function getSustainabilityLevel($whatIf)
    {
        $marginOfSafety = $whatIf['margin_of_safety'];
        if ($marginOfSafety > 40) return 'excellent';
        if ($marginOfSafety > 20) return 'good';
        return 'risky';
    }

    private function getProfitabilityIcon($margin)
    {
        if ($margin > 20) return 'bi-trophy text-success';
        if ($margin > 10) return 'bi-check-circle text-success';
        if ($margin > 0) return 'bi-exclamation-triangle text-warning';
        return 'bi-x-circle text-danger';
    }

    private function getRiskLevel($score)
    {
        if ($score <= 2) return 'low';
        if ($score <= 5) return 'medium';
        if ($score <= 8) return 'high';
        return 'critical';
    }

    private function getOverallRiskAssessment($score)
    {
        if ($score <= 2) return 'Bisnis dalam kondisi aman dengan risiko rendah.';
        if ($score <= 5) return 'Bisnis memiliki beberapa risiko yang perlu dipantau.';
        if ($score <= 8) return 'Bisnis menghadapi risiko tinggi yang memerlukan perhatian segera.';
        return 'Bisnis dalam kondisi kritis dan memerlukan tindakan darurat.';
    }

    private function getImmediateActions($whatIf, $actual)
    {
        $actions = [];
        
        if ($whatIf['profit_margin'] < 0) {
            $actions[] = 'Evaluasi dan kurangi biaya operasional segera';
            $actions[] = 'Tinjau ulang harga jual produk';
        }
        
        if ($whatIf['break_even_units'] > $whatIf['units']) {
            $actions[] = 'Fokus pada peningkatan volume penjualan';
            $actions[] = 'Negosiasi ulang kontrak dengan supplier';
        }
        
        return $actions;
    }

    private function getShortTermActions($whatIf, $actual)
    {
        $actions = [];
        
        $actions[] = 'Implementasi strategi marketing untuk meningkatkan penjualan';
        $actions[] = 'Optimasi proses produksi untuk efisiensi';
        $actions[] = 'Diversifikasi produk atau layanan';
        $actions[] = 'Bangun relasi dengan supplier yang lebih baik';
        
        return $actions;
    }

    private function getLongTermActions($whatIf, $actual)
    {
        $actions = [];
        
        $actions[] = 'Kembangkan strategi bisnis jangka panjang';
        $actions[] = 'Investasi dalam teknologi dan inovasi';
        $actions[] = 'Ekspansi ke pasar atau segmen baru';
        $actions[] = 'Bangun brand dan customer loyalty';
        
        return $actions;
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
