<?php

namespace App\Livewire\Analysis;

use Livewire\Component;
use App\Models\Income;
use App\Models\Expenditure;
use App\Models\FixedCost;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BepCalculator extends Component
{
    // Properties untuk input
    public $selectedMonth;
    public $selectedYear;
    public $selectedProduct = '';
    public $customSellingPrice = 0;
    public $customVariableCost = 0;
    public $customFixedCost = 0;
    public $targetProfit = 0;

    // Properties untuk Target BEP (akan dihitung otomatis)
    public $targetBepUnits = 0;
    public $targetBepRevenue = 0;
    public $currentUnits = 0;
    public $currentRevenue = 0;
    public $remainingUnits = 0;
    public $remainingRevenue = 0;
    public $unitProgress = 0;
    public $revenueProgress = 0;

    // Mutator untuk menangani input yang sudah diformat
    public function setTargetBepRevenueAttribute($value)
    {
        // Remove formatting (dots and commas) and convert to numeric
        $cleanValue = str_replace(['.', ','], '', $value);
        $this->attributes['targetBepRevenue'] = is_numeric($cleanValue) ? (float)$cleanValue : 0;
    }

    // Properties untuk hasil perhitungan
    public $calculationResult = null;
    public $calculationError = null;
    public $dataSummary = null;
    public $productList = [];

    // Properties untuk mode
    public $calculationMode = 'period'; // 'period', 'product', 'custom'

    // Properties untuk validasi
    public $dataAvailable = false;
    public $dataValidation = null;

    // Properties untuk penjelasan BEP yang mudah dipahami
    public $bepExplanation = null;
    public $bepRecommendations = [];
    public $bepInsights = [];
    public $riskAssessment = null;

    public function mount()
    {
        $this->selectedMonth = now()->month;
        $this->selectedYear = now()->year;
        $this->loadProductList();
        $this->validateDataAvailability();
    }

    public function loadProductList()
    {
        try {
            $this->productList = Income::query()
                ->whereNotNull('produk')
                ->where('produk', '!=', '')
                ->distinct()
                ->orderBy('produk')
                ->pluck('produk')
                ->toArray();
        } catch (\Exception $e) {
            $this->productList = [];
            Log::error('Error loading product list: ' . $e->getMessage());
        }
    }

    public function validateDataAvailability()
    {
        if (!Auth::check() || !$this->selectedMonth || !$this->selectedYear) {
            $this->dataAvailable = false;
            $this->dataValidation = [
                'available' => false,
                'message' => 'Silakan pilih bulan dan tahun terlebih dahulu.'
            ];
            return;
        }

        try {
            $year = (int)$this->selectedYear;
            $month = (int)$this->selectedMonth;

            // Cek ketersediaan data biaya tetap
            $fixedCostCount = FixedCost::query()
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->count();

            // Cek ketersediaan data penjualan
            $incomeCount = Income::query()
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->count();

            // Cek ketersediaan data pengeluaran
            $expenditureCount = Expenditure::query()
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->count();

            $messages = [];
            if ($fixedCostCount === 0) {
                $messages[] = 'Tidak ada data biaya tetap untuk periode ini.';
            }
            if ($incomeCount === 0) {
                $messages[] = 'Tidak ada data penjualan untuk periode ini.';
            }
            if ($expenditureCount === 0) {
                $messages[] = 'Tidak ada data pengeluaran untuk periode ini.';
            }

            if (!empty($messages)) {
                $this->dataAvailable = false;
                $this->dataValidation = [
                    'available' => false,
                    'message' => implode(' ', $messages) . ' Silakan pilih periode lain atau tambahkan data yang diperlukan.'
                ];
            } else {
                $this->dataAvailable = true;
                $this->dataValidation = [
                    'available' => true,
                    'message' => 'Data tersedia untuk perhitungan BEP.',
                    'fixed_cost_count' => $fixedCostCount,
                    'income_count' => $incomeCount,
                    'expenditure_count' => $expenditureCount
                ];
            }
        } catch (\Exception $e) {
            $this->dataAvailable = false;
            $this->dataValidation = [
                'available' => false,
                'message' => 'Terjadi kesalahan saat memvalidasi data: ' . $e->getMessage()
            ];
        }
    }

    public function updatedSelectedMonth()
    {
        $this->validateDataAvailability();
        $this->resetCalculation();
    }

    public function updatedSelectedYear()
    {
        $this->validateDataAvailability();
        $this->resetCalculation();
    }

    public function updatedSelectedProduct()
    {
        if ($this->selectedProduct) {
            $this->loadProductData();
        }
        $this->resetCalculation();
    }

    public function updatedCalculationMode()
    {
        $this->resetCalculation();
        $this->validateDataAvailability();
    }

    public function loadProductData()
    {
        if (!$this->selectedProduct) return;

        try {
            $year = (int)$this->selectedYear;
            $month = (int)$this->selectedMonth;

            // Harga jual rata-rata untuk produk tertentu
            $avgSellingPrice = Income::query()
                ->where('produk', $this->selectedProduct)
                ->when($this->selectedYear, fn($q) => $q->whereYear('tanggal', $year))
                ->when($this->selectedMonth, fn($q) => $q->whereMonth('tanggal', $month))
                ->avg('harga_satuan');

            if ($avgSellingPrice && $avgSellingPrice > 0) {
                $this->customSellingPrice = (float) $avgSellingPrice;
            } else {
                // Fallback ke rata-rata keseluruhan
                $this->customSellingPrice = (float) Income::query()
                    ->where('produk', $this->selectedProduct)
                    ->avg('harga_satuan');
            }

            // Biaya variabel rata-rata untuk produk tertentu
            $avgVariableCost = Expenditure::query()
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->avg('jumlah');

            if ($avgVariableCost && $avgVariableCost > 0) {
                $this->customVariableCost = (float) $avgVariableCost;
            }
        } catch (\Exception $e) {
            Log::error('Error loading product data: ' . $e->getMessage());
        }
    }

    public function calculateBep()
    {
        $this->resetCalculation();

        if (!$this->dataAvailable) {
            $this->calculationError = 'Data tidak tersedia untuk perhitungan BEP.';
            return;
        }

        try {
            $year = (int)$this->selectedYear;
            $month = (int)$this->selectedMonth;

            switch ($this->calculationMode) {
                case 'period':
                    $this->calculatePeriodBep($year, $month);
                    break;
                case 'product':
                    $this->calculateProductBep($year, $month);
                    break;
                case 'custom':
                    $this->calculateCustomBep();
                    break;
                default:
                    $this->calculationError = 'Mode perhitungan tidak valid.';
                    return;
            }

            $this->loadDataSummary($year, $month);
            $this->generateBepExplanation();
            $this->generateRecommendations();
            $this->generateInsights();
            $this->assessRisk();

        } catch (\Exception $e) {
            $this->calculationError = 'Terjadi kesalahan saat menghitung BEP: ' . $e->getMessage();
            Log::error('Error calculating BEP: ' . $e->getMessage());
        }
    }

    private function calculatePeriodBep($year, $month)
    {
        // 1. Biaya Tetap untuk periode yang dipilih
        $totalFixedCost = (float) FixedCost::query()
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->sum('nominal');

        // 2. Total Penjualan untuk periode yang dipilih
        $totalSales = (float) Income::query()
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->get()
            ->sum(function ($income) {
                return ((float) ($income->jumlah_terjual ?? 0)) * ((float) ($income->harga_satuan ?? 0));
            });

        // 3. Total Pengeluaran (Biaya Variabel) untuk periode yang dipilih
        $totalVariableCost = (float) Expenditure::query()
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->sum('jumlah');

        // 4. Total Unit Terjual
        $totalUnitsSold = (int) Income::query()
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->sum('jumlah_terjual');

        if ($totalUnitsSold <= 0) {
            $this->calculationError = 'Tidak ada unit terjual pada periode ini.';
            return;
        }

        // 5. Rata-rata harga jual per unit
        $avgSellingPrice = $totalSales / $totalUnitsSold;

        // 6. Rata-rata biaya variabel per unit
        $avgVariableCostPerUnit = $totalVariableCost / $totalUnitsSold;

        // 7. Hitung BEP
        $contributionMargin = $avgSellingPrice - $avgVariableCostPerUnit;

        if ($contributionMargin <= 0) {
            $this->calculationError = 'Biaya variabel per unit lebih besar atau sama dengan harga jual. Tidak bisa mencapai BEP.';
            return;
        }

        $bepUnits = ceil($totalFixedCost / $contributionMargin);
        $bepRevenue = $bepUnits * $avgSellingPrice;

        $this->calculationResult = [
            'mode' => 'Periode',
            'period' => Carbon::create($year, $month, 1)->format('F Y'),
            'fixed_cost' => $totalFixedCost,
            'variable_cost_total' => $totalVariableCost,
            'variable_cost_per_unit' => $avgVariableCostPerUnit,
            'selling_price_per_unit' => $avgSellingPrice,
            'contribution_margin' => $contributionMargin,
            'units_sold' => $totalUnitsSold,
            'total_sales' => $totalSales,
            'bep_units' => $bepUnits,
            'bep_revenue' => $bepRevenue,
            'profit_loss' => $totalSales - $totalVariableCost - $totalFixedCost,
            'margin_of_safety_units' => max(0, $totalUnitsSold - $bepUnits),
            'margin_of_safety_percentage' => $totalUnitsSold > 0 ? (($totalUnitsSold - $bepUnits) / $totalUnitsSold) * 100 : 0
        ];
    }

    private function calculateProductBep($year, $month)
    {
        if (!$this->selectedProduct) {
            $this->calculationError = 'Silakan pilih produk terlebih dahulu.';
            return;
        }

        // 1. Biaya Tetap untuk periode yang dipilih
        $totalFixedCost = (float) FixedCost::query()
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->sum('nominal');

        // 2. Data penjualan untuk produk tertentu
        $productIncomes = Income::query()
            ->where('produk', $this->selectedProduct)
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->get();

        if ($productIncomes->isEmpty()) {
            $this->calculationError = 'Tidak ada data penjualan untuk produk ' . $this->selectedProduct . ' pada periode ini.';
            return;
        }

        // 3. Total penjualan dan unit untuk produk tertentu
        $totalSales = $productIncomes->sum(function ($income) {
            return ((float) ($income->jumlah_terjual ?? 0)) * ((float) ($income->harga_satuan ?? 0));
        });

        $totalUnitsSold = $productIncomes->sum('jumlah_terjual');

        // 4. Rata-rata harga jual per unit
        $avgSellingPrice = $totalSales / $totalUnitsSold;

        // 5. Analisis biaya variabel yang lebih akurat per produk
        $productVariableCost = $this->calculateProductVariableCost($year, $month, $this->selectedProduct);
        $avgVariableCostPerUnit = $productVariableCost / $totalUnitsSold;

        // 6. Hitung BEP
        $contributionMargin = $avgSellingPrice - $avgVariableCostPerUnit;

        if ($contributionMargin <= 0) {
            $this->calculationError = 'Biaya variabel per unit lebih besar atau sama dengan harga jual. Tidak bisa mencapai BEP.';
            return;
        }

        $bepUnits = ceil($totalFixedCost / $contributionMargin);
        $bepRevenue = $bepUnits * $avgSellingPrice;

        // 7. Analisis produk yang lebih mendalam
        $productAnalysis = $this->analyzeProductPerformance($productIncomes, $avgSellingPrice, $avgVariableCostPerUnit);
        $productTrends = $this->getProductTrends($year, $month, $this->selectedProduct);
        $productComparison = $this->compareProductWithOthers($year, $month, $this->selectedProduct);

        $this->calculationResult = [
            'mode' => 'Produk',
            'product' => $this->selectedProduct,
            'period' => Carbon::create($year, $month, 1)->format('F Y'),
            'fixed_cost' => $totalFixedCost,
            'variable_cost_total' => $productVariableCost,
            'variable_cost_per_unit' => $avgVariableCostPerUnit,
            'selling_price_per_unit' => $avgSellingPrice,
            'contribution_margin' => $contributionMargin,
            'units_sold' => $totalUnitsSold,
            'total_sales' => $totalSales,
            'bep_units' => $bepUnits,
            'bep_revenue' => $bepRevenue,
            'profit_loss' => $totalSales - $productVariableCost - $totalFixedCost,
            'margin_of_safety_units' => max(0, $totalUnitsSold - $bepUnits),
            'margin_of_safety_percentage' => $totalUnitsSold > 0 ? (($totalUnitsSold - $bepUnits) / $totalUnitsSold) * 100 : 0,
            'product_analysis' => $productAnalysis,
            'product_trends' => $productTrends,
            'product_comparison' => $productComparison
        ];
    }

    private function calculateCustomBep()
    {
        if ($this->customSellingPrice <= 0) {
            $this->calculationError = 'Harga jual harus lebih dari 0.';
            return;
        }

        if ($this->customVariableCost < 0) {
            $this->calculationError = 'Biaya variabel tidak boleh negatif.';
            return;
        }

        if ($this->customFixedCost < 0) {
            $this->calculationError = 'Biaya tetap tidak boleh negatif.';
            return;
        }

        $contributionMargin = $this->customSellingPrice - $this->customVariableCost;

        if ($contributionMargin <= 0) {
            $this->calculationError = 'Biaya variabel per unit lebih besar atau sama dengan harga jual. Tidak bisa mencapai BEP.';
            return;
        }

        $bepUnits = ceil($this->customFixedCost / $contributionMargin);
        $bepRevenue = $bepUnits * $this->customSellingPrice;

        // Hitung BEP dengan target profit
        $targetProfitUnits = 0;
        $targetProfitRevenue = 0;
        
        if ($this->targetProfit > 0) {
            $targetProfitUnits = ceil(($this->customFixedCost + $this->targetProfit) / $contributionMargin);
            $targetProfitRevenue = $targetProfitUnits * $this->customSellingPrice;
        }

        // Analisis skenario custom yang lebih mendalam
        $scenarioAnalysis = $this->analyzeCustomScenarios();
        $sensitivityAnalysis = $this->performSensitivityAnalysis();
        $whatIfScenarios = $this->generateWhatIfScenarios();

        $this->calculationResult = [
            'mode' => 'Custom',
            'fixed_cost' => $this->customFixedCost,
            'variable_cost_per_unit' => $this->customVariableCost,
            'selling_price_per_unit' => $this->customSellingPrice,
            'contribution_margin' => $contributionMargin,
            'bep_units' => $bepUnits,
            'bep_revenue' => $bepRevenue,
            'target_profit' => $this->targetProfit,
            'target_profit_units' => $targetProfitUnits,
            'target_profit_revenue' => $targetProfitRevenue,
            'scenario_analysis' => $scenarioAnalysis,
            'sensitivity_analysis' => $sensitivityAnalysis,
            'what_if_scenarios' => $whatIfScenarios
        ];
    }

    private function loadDataSummary($year, $month)
    {
        try {
            $fixedCostTotal = FixedCost::query()
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->sum('nominal');

            $incomeTotal = Income::query()
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->get()
                ->sum(function ($income) {
                    return ((float) ($income->jumlah_terjual ?? 0)) * ((float) ($income->harga_satuan ?? 0));
                });

            $expenditureTotal = Expenditure::query()
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->sum('jumlah');

            $this->dataSummary = [
                'period' => Carbon::create($year, $month, 1)->format('F Y'),
                'fixed_cost' => $fixedCostTotal,
                'income' => $incomeTotal,
                'expenditure' => $expenditureTotal,
                'profit' => $incomeTotal - $expenditureTotal
            ];

            // Hitung target BEP otomatis berdasarkan data yang ada
            $this->calculateTargetBepAutomatically($year, $month, $incomeTotal, $fixedCostTotal);
        } catch (\Exception $e) {
            Log::error('Error loading data summary: ' . $e->getMessage());
        }
    }

    /**
     * Menghitung target BEP secara otomatis berdasarkan data yang ada
     */
    private function calculateTargetBepAutomatically($year, $month, $incomeTotal, $fixedCostTotal)
    {
        try {
            // Hitung total unit terjual untuk periode ini
            $this->currentUnits = (int) Income::query()
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->sum('jumlah_terjual');

            // Set current revenue dari data penjualan aktual, bukan BEP revenue
            $this->currentRevenue = $incomeTotal;

            // Hitung target BEP otomatis berdasarkan tren dan data historis
            $this->calculateSmartTargets($year, $month, $incomeTotal, $fixedCostTotal);

            // Hitung sisa dan progress
            $this->calculateRemainingAndProgress();

        } catch (\Exception $e) {
            Log::error('Error calculating target BEP automatically: ' . $e->getMessage());
        }
    }

    /**
     * Menghitung target BEP yang smart berdasarkan data historis
     */
    private function calculateSmartTargets($year, $month, $incomeTotal, $fixedCostTotal)
    {
        try {
            // Target 1: BEP + 20% (target realistis)
            $realisticTargetUnits = ceil(($this->calculationResult['bep_units'] ?? 0) * 1.2);
            $realisticTargetRevenue = ($realisticTargetUnits * ($this->calculationResult['selling_price_per_unit'] ?? 0));

            // Target 2: BEP + 50% (target optimis)
            $optimisticTargetUnits = ceil(($this->calculationResult['bep_units'] ?? 0) * 1.5);
            $optimisticTargetRevenue = ($optimisticTargetUnits * ($this->calculationResult['selling_price_per_unit'] ?? 0));

            // Target 3: Berdasarkan tren bulan sebelumnya
            $previousMonth = $month == 1 ? 12 : $month - 1;
            $previousYear = $month == 1 ? $year - 1 : $year;
            
            $previousIncome = Income::query()
                ->whereYear('tanggal', $previousYear)
                ->whereMonth('tanggal', $previousMonth)
                ->get()
                ->sum(function ($income) {
                    return ((float) ($income->jumlah_terjual ?? 0)) * ((float) ($income->harga_satuan ?? 0));
                });

            $trendTargetRevenue = $previousIncome > 0 ? $previousIncome * 1.1 : $realisticTargetRevenue; // 10% growth
            $trendTargetUnits = ceil($trendTargetRevenue / ($this->calculationResult['selling_price_per_unit'] ?? 1));

            // Pilih target yang paling sesuai
            if ($this->currentUnits > 0) {
                // Jika sudah ada penjualan, gunakan target yang lebih ambisius
                $this->targetBepUnits = max($realisticTargetUnits, $trendTargetUnits);
                $this->targetBepRevenue = max($realisticTargetRevenue, $trendTargetRevenue);
            } else {
                // Jika belum ada penjualan, gunakan target realistis
                $this->targetBepUnits = $realisticTargetUnits;
                $this->targetBepRevenue = $realisticTargetRevenue;
            }

            // Pastikan target tidak terlalu rendah
            $this->targetBepUnits = max($this->targetBepUnits, ($this->calculationResult['bep_units'] ?? 0) + 5);
            $this->targetBepRevenue = max($this->targetBepRevenue, ($this->calculationResult['bep_revenue'] ?? 0) * 1.1);

        } catch (\Exception $e) {
            Log::error('Error calculating smart targets: ' . $e->getMessage());
            // Fallback ke target sederhana
            $this->targetBepUnits = ($this->calculationResult['bep_units'] ?? 0) + 10;
            $this->targetBepRevenue = ($this->calculationResult['bep_revenue'] ?? 0) * 1.2;
        }
    }

    /**
     * Menghitung sisa dan progress ke target
     */
    private function calculateRemainingAndProgress()
    {
        // Hitung sisa unit ke target
        $this->remainingUnits = max(0, $this->targetBepUnits - $this->currentUnits);
        
        // Hitung sisa revenue ke target - gunakan data penjualan aktual
        $actualRevenue = $this->calculationResult['total_sales'] ?? 0;
        $this->remainingRevenue = max(0, $this->targetBepRevenue - $actualRevenue);

        // Hitung progress unit (dalam persentase)
        $this->unitProgress = $this->targetBepUnits > 0 
            ? min(100, ($this->currentUnits / $this->targetBepUnits) * 100) 
            : 0;

        // Hitung progress revenue (dalam persentase) - gunakan data penjualan aktual
        $this->revenueProgress = $this->targetBepRevenue > 0 
            ? min(100, ($actualRevenue / $this->targetBepRevenue) * 100) 
            : 0;
    }

    public function resetCalculation()
    {
        $this->calculationResult = null;
        $this->calculationError = null;
        $this->targetBepUnits = 0;
        $this->targetBepRevenue = 0;
        $this->currentUnits = 0;
        $this->currentRevenue = 0;
        $this->remainingUnits = 0;
        $this->remainingRevenue = 0;
        $this->unitProgress = 0;
        $this->revenueProgress = 0;
        $this->bepExplanation = null;
        $this->bepRecommendations = [];
        $this->bepInsights = [];
        $this->riskAssessment = null;
    }

    /**
     * Update target BEP manual dan hitung ulang sisa dan progress
     */
    public function updateTargetBep()
    {
        try {
            if ($this->targetBepUnits > 0 || $this->targetBepRevenue > 0) {
                $this->calculateRemainingAndProgress();
            }
        } catch (\Exception $e) {
            Log::error('Error updating target BEP: ' . $e->getMessage());
        }
    }

    /**
     * Generate penjelasan BEP yang mudah dipahami
     */
    private function generateBepExplanation()
    {
        if (!$this->calculationResult) return;

        $result = $this->calculationResult;
        $bepUnits = $result['bep_units'] ?? 0;
        $bepRevenue = $result['bep_revenue'] ?? 0;
        $contributionMargin = $result['contribution_margin'] ?? 0;
        $sellingPrice = $result['selling_price_per_unit'] ?? 0;
        $variableCost = $result['variable_cost_per_unit'] ?? 0;

        $this->bepExplanation = [
            'simple_explanation' => "Untuk mencapai titik impas (BEP), Anda perlu menjual minimal " . 
                number_format($bepUnits, 0, ',', '.') . " unit produk atau mencapai penjualan sebesar Rp " . 
                number_format($bepRevenue, 0, ',', '.') . ".",
            
            'detailed_breakdown' => [
                'fixed_cost_explanation' => "Biaya tetap Anda sebesar Rp " . 
                    number_format($result['fixed_cost'] ?? 0, 0, ',', '.') . 
                    " harus ditutup oleh keuntungan dari setiap unit yang dijual.",
                
                'contribution_explanation' => "Setiap unit yang dijual memberikan kontribusi sebesar Rp " . 
                    number_format($contributionMargin, 0, ',', '.') . 
                    " (harga jual Rp " . number_format($sellingPrice, 0, ',', '.') . 
                    " dikurangi biaya variabel Rp " . number_format($variableCost, 0, ',', '.') . ").",
                
                'calculation_explanation' => "Untuk menutup biaya tetap, Anda perlu: " .
                    "Biaya Tetap (Rp " . number_format($result['fixed_cost'] ?? 0, 0, ',', '.') . ") " .
                    "÷ Kontribusi per Unit (Rp " . number_format($contributionMargin, 0, ',', '.') . ") " .
                    "= " . number_format($bepUnits, 0, ',', '.') . " unit"
            ],
            
            'practical_meaning' => $this->getPracticalMeaning($result),
            'daily_target' => $this->getDailyTarget($bepUnits, $bepRevenue),
            'weekly_target' => $this->getWeeklyTarget($bepUnits, $bepRevenue)
        ];
    }

    /**
     * Generate rekomendasi bisnis berdasarkan hasil BEP
     */
    private function generateRecommendations()
    {
        if (!$this->calculationResult) return;

        $result = $this->calculationResult;
        $recommendations = [];

        // Analisis Margin of Safety
        $marginOfSafety = $result['margin_of_safety_percentage'] ?? 0;
        if ($marginOfSafety < 20) {
            $recommendations[] = [
                'type' => 'warning',
                'title' => 'Margin Keamanan Rendah',
                'description' => 'Margin keamanan Anda hanya ' . number_format($marginOfSafety, 1) . '%. Ini berarti bisnis Anda rentan terhadap penurunan penjualan.',
                'actions' => [
                    'Tingkatkan volume penjualan',
                    'Kurangi biaya variabel per unit',
                    'Pertimbangkan menaikkan harga jual',
                    'Optimalisasi biaya tetap'
                ]
            ];
        }

        // Analisis Contribution Margin
        $contributionMarginRatio = ($result['contribution_margin'] ?? 0) / ($result['selling_price_per_unit'] ?? 1) * 100;
        if ($contributionMarginRatio < 30) {
            $recommendations[] = [
                'type' => 'info',
                'title' => 'Rasio Kontribusi Perlu Diperbaiki',
                'description' => 'Rasio kontribusi Anda ' . number_format($contributionMarginRatio, 1) . '%. Idealnya di atas 30%.',
                'actions' => [
                    'Negosiasi harga bahan baku untuk menurunkan biaya variabel',
                    'Cari supplier yang lebih murah',
                    'Tingkatkan efisiensi produksi',
                    'Pertimbangkan menaikkan harga jual jika pasar memungkinkan'
                ]
            ];
        }

        // Analisis Profitabilitas Saat Ini
        $currentProfit = $result['profit_loss'] ?? 0;
        if ($currentProfit < 0) {
            $recommendations[] = [
                'type' => 'danger',
                'title' => 'Bisnis Mengalami Kerugian',
                'description' => 'Saat ini bisnis Anda rugi Rp ' . number_format(abs($currentProfit), 0, ',', '.') . '. Tindakan segera diperlukan.',
                'actions' => [
                    'Fokus mencapai BEP terlebih dahulu',
                    'Review dan potong biaya yang tidak perlu',
                    'Tingkatkan strategi pemasaran untuk meningkatkan penjualan',
                    'Pertimbangkan diversifikasi produk'
                ]
            ];
        } elseif ($currentProfit > 0) {
            $recommendations[] = [
                'type' => 'success',
                'title' => 'Bisnis Sudah Menguntungkan',
                'description' => 'Selamat! Bisnis Anda sudah meraih keuntungan Rp ' . number_format($currentProfit, 0, ',', '.') . '.',
                'actions' => [
                    'Pertahankan kinerja saat ini',
                    'Pertimbangkan ekspansi bisnis',
                    'Alokasikan sebagian keuntungan untuk cadangan',
                    'Investasi untuk meningkatkan kapasitas produksi'
                ]
            ];
        }

        $this->bepRecommendations = $recommendations;
    }

    /**
     * Generate insights bisnis dari analisis BEP
     */
    private function generateInsights()
    {
        if (!$this->calculationResult) return;

        $result = $this->calculationResult;
        $insights = [];

        // Insight tentang efisiensi
        $contributionMargin = $result['contribution_margin'] ?? 0;
        $sellingPrice = $result['selling_price_per_unit'] ?? 1;
        $efficiency = ($contributionMargin / $sellingPrice) * 100;

        $insights[] = [
            'icon' => 'bi-graph-up',
            'title' => 'Efisiensi Operasional',
            'value' => number_format($efficiency, 1) . '%',
            'description' => 'Dari setiap Rp 100 penjualan, Rp ' . number_format($efficiency, 0) . ' berkontribusi untuk menutup biaya tetap dan keuntungan.',
            'status' => $efficiency >= 50 ? 'excellent' : ($efficiency >= 30 ? 'good' : 'needs_improvement')
        ];

        // Insight tentang waktu mencapai BEP
        $unitsPerDay = ($result['units_sold'] ?? 0) / 30; // Asumsi 30 hari per bulan
        $daysToBreakeven = $unitsPerDay > 0 ? ($result['bep_units'] ?? 0) / $unitsPerDay : 0;

        $insights[] = [
            'icon' => 'bi-calendar-check',
            'title' => 'Waktu Mencapai BEP',
            'value' => $daysToBreakeven > 0 ? number_format($daysToBreakeven, 0) . ' hari' : 'N/A',
            'description' => 'Berdasarkan rata-rata penjualan saat ini, Anda membutuhkan ' . number_format($daysToBreakeven, 0) . ' hari untuk mencapai BEP.',
            'status' => $daysToBreakeven <= 20 ? 'excellent' : ($daysToBreakeven <= 30 ? 'good' : 'needs_improvement')
        ];

        // Insight tentang leverage operasional
        $operatingLeverage = $this->calculateOperatingLeverage($result);
        $insights[] = [
            'icon' => 'bi-speedometer2',
            'title' => 'Leverage Operasional',
            'value' => $operatingLeverage > 0 ? number_format($operatingLeverage, 1) . 'x' : 'N/A',
            'description' => 'Setiap 1% peningkatan penjualan akan meningkatkan laba sekitar ' . number_format($operatingLeverage, 1) . '%.',
            'status' => $operatingLeverage >= 2 ? 'excellent' : ($operatingLeverage >= 1.5 ? 'good' : 'moderate')
        ];

        $this->bepInsights = $insights;
    }

    /**
     * Assess risiko bisnis berdasarkan analisis BEP
     */
    private function assessRisk()
    {
        if (!$this->calculationResult) return;

        $result = $this->calculationResult;
        $riskFactors = [];
        $overallRisk = 'low';

        // Risk Factor 1: Margin of Safety
        $marginOfSafety = $result['margin_of_safety_percentage'] ?? 0;
        if ($marginOfSafety < 10) {
            $riskFactors[] = [
                'factor' => 'Margin Keamanan Sangat Rendah',
                'level' => 'high',
                'description' => 'Penurunan penjualan kecil saja bisa menyebabkan kerugian.',
                'impact' => 'Risiko kerugian sangat tinggi'
            ];
            $overallRisk = 'high';
        } elseif ($marginOfSafety < 20) {
            $riskFactors[] = [
                'factor' => 'Margin Keamanan Rendah',
                'level' => 'medium',
                'description' => 'Bisnis cukup rentan terhadap fluktuasi penjualan.',
                'impact' => 'Perlu monitoring ketat'
            ];
            $overallRisk = $overallRisk === 'low' ? 'medium' : $overallRisk;
        }

        // Risk Factor 2: Fixed Cost Ratio
        $fixedCostRatio = ($result['fixed_cost'] ?? 0) / (($result['total_sales'] ?? 0) ?: 1) * 100;
        if ($fixedCostRatio > 40) {
            $riskFactors[] = [
                'factor' => 'Rasio Biaya Tetap Tinggi',
                'level' => 'medium',
                'description' => 'Biaya tetap ' . number_format($fixedCostRatio, 1) . '% dari penjualan.',
                'impact' => 'Sulit beradaptasi dengan penurunan penjualan'
            ];
            $overallRisk = $overallRisk === 'low' ? 'medium' : $overallRisk;
        }

        // Risk Factor 3: Contribution Margin
        $contributionMarginRatio = ($result['contribution_margin'] ?? 0) / ($result['selling_price_per_unit'] ?? 1) * 100;
        if ($contributionMarginRatio < 20) {
            $riskFactors[] = [
                'factor' => 'Kontribusi Margin Rendah',
                'level' => 'high',
                'description' => 'Keuntungan per unit sangat kecil (' . number_format($contributionMarginRatio, 1) . '%).',
                'impact' => 'Sangat sensitif terhadap kenaikan biaya'
            ];
            $overallRisk = 'high';
        }

        $this->riskAssessment = [
            'overall_risk' => $overallRisk,
            'risk_factors' => $riskFactors,
            'risk_score' => $this->calculateRiskScore($result),
            'mitigation_strategies' => $this->getRiskMitigationStrategies($overallRisk, $riskFactors)
        ];
    }

    /**
     * Calculate product-specific variable cost
     */
    private function calculateProductVariableCost($year, $month, $productName)
    {
        try {
            // Coba estimasi biaya variabel berdasarkan pengeluaran yang relevan
            $totalExpenditure = (float) Expenditure::query()
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->sum('jumlah');

            // Estimasi berdasarkan proporsi penjualan produk
            $totalSales = Income::query()
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->get()
                ->sum(function ($income) {
                    return ((float) ($income->jumlah_terjual ?? 0)) * ((float) ($income->harga_satuan ?? 0));
                });

            $productSales = Income::query()
                ->where('produk', $productName)
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->get()
                ->sum(function ($income) {
                    return ((float) ($income->jumlah_terjual ?? 0)) * ((float) ($income->harga_satuan ?? 0));
                });

            if ($totalSales > 0) {
                $productRatio = $productSales / $totalSales;
                return $totalExpenditure * $productRatio;
            }

            return $totalExpenditure;
        } catch (\Exception $e) {
            Log::error('Error calculating product variable cost: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Analyze product performance
     */
    private function analyzeProductPerformance($productIncomes, $avgSellingPrice, $avgVariableCost)
    {
        $analysis = [];

        // Analisis harga jual
        $prices = $productIncomes->pluck('harga_satuan')->filter()->toArray();
        if (!empty($prices)) {
            $analysis['price_analysis'] = [
                'min_price' => min($prices),
                'max_price' => max($prices),
                'avg_price' => array_sum($prices) / count($prices),
                'price_consistency' => count(array_unique($prices)) === 1 ? 'Konsisten' : 'Bervariasi'
            ];
        }

        // Analisis volume penjualan
        $volumes = $productIncomes->pluck('jumlah_terjual')->filter()->toArray();
        if (!empty($volumes)) {
            $analysis['volume_analysis'] = [
                'min_volume' => min($volumes),
                'max_volume' => max($volumes),
                'avg_volume' => array_sum($volumes) / count($volumes),
                'total_transactions' => count($volumes)
            ];
        }

        // Analisis profitabilitas
        $contributionMarginRatio = ($avgSellingPrice - $avgVariableCost) / $avgSellingPrice * 100;
        $analysis['profitability'] = [
            'contribution_margin_ratio' => $contributionMarginRatio,
            'profitability_level' => $this->getProfitabilityLevel($contributionMarginRatio),
            'recommendation' => $this->getProductRecommendation($contributionMarginRatio)
        ];

        return $analysis;
    }

    /**
     * Get product trends
     */
    private function getProductTrends($year, $month, $productName)
    {
        try {
            // Data 3 bulan terakhir
            $trends = [];
            for ($i = 2; $i >= 0; $i--) {
                $targetMonth = $month - $i;
                $targetYear = $year;
                
                if ($targetMonth <= 0) {
                    $targetMonth += 12;
                    $targetYear -= 1;
                }

                $monthlyData = Income::query()
                    ->where('produk', $productName)
                    ->whereYear('tanggal', $targetYear)
                    ->whereMonth('tanggal', $targetMonth)
                    ->get();

                $monthlySales = $monthlyData->sum(function ($income) {
                    return ((float) ($income->jumlah_terjual ?? 0)) * ((float) ($income->harga_satuan ?? 0));
                });

                $monthlyUnits = $monthlyData->sum('jumlah_terjual');

                $trends[] = [
                    'month' => Carbon::create($targetYear, $targetMonth, 1)->format('M Y'),
                    'sales' => $monthlySales,
                    'units' => $monthlyUnits,
                    'avg_price' => $monthlyUnits > 0 ? $monthlySales / $monthlyUnits : 0
                ];
            }

            // Hitung trend
            if (count($trends) >= 2) {
                $currentSales = $trends[2]['sales'] ?? 0;
                $previousSales = $trends[1]['sales'] ?? 0;
                $salesGrowth = $previousSales > 0 ? (($currentSales - $previousSales) / $previousSales) * 100 : 0;

                $currentUnits = $trends[2]['units'] ?? 0;
                $previousUnits = $trends[1]['units'] ?? 0;
                $unitsGrowth = $previousUnits > 0 ? (($currentUnits - $previousUnits) / $previousUnits) * 100 : 0;

                return [
                    'monthly_data' => $trends,
                    'sales_growth' => $salesGrowth,
                    'units_growth' => $unitsGrowth,
                    'trend_direction' => $salesGrowth > 0 ? 'Naik' : ($salesGrowth < 0 ? 'Turun' : 'Stabil'),
                    'trend_strength' => abs($salesGrowth) > 20 ? 'Kuat' : (abs($salesGrowth) > 10 ? 'Sedang' : 'Lemah')
                ];
            }

            return ['monthly_data' => $trends];
        } catch (\Exception $e) {
            Log::error('Error getting product trends: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Compare product with others
     */
    private function compareProductWithOthers($year, $month, $productName)
    {
        try {
            // Data semua produk
            $allProducts = Income::query()
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->get()
                ->groupBy('produk');

            $productStats = [];
            foreach ($allProducts as $product => $incomes) {
                $totalSales = $incomes->sum(function ($income) {
                    return ((float) ($income->jumlah_terjual ?? 0)) * ((float) ($income->harga_satuan ?? 0));
                });
                $totalUnits = $incomes->sum('jumlah_terjual');
                $avgPrice = $totalUnits > 0 ? $totalSales / $totalUnits : 0;

                $productStats[] = [
                    'product' => $product,
                    'sales' => $totalSales,
                    'units' => $totalUnits,
                    'avg_price' => $avgPrice
                ];
            }

            // Sort by sales
            usort($productStats, function($a, $b) {
                return $b['sales'] <=> $a['sales'];
            });

            // Find current product position
            $currentProductIndex = -1;
            foreach ($productStats as $index => $stat) {
                if ($stat['product'] === $productName) {
                    $currentProductIndex = $index;
                    break;
                }
            }

            if ($currentProductIndex >= 0) {
                $totalProducts = count($productStats);
                $rank = $currentProductIndex + 1;
                $percentile = (($totalProducts - $rank + 1) / $totalProducts) * 100;

                return [
                    'rank' => $rank,
                    'total_products' => $totalProducts,
                    'percentile' => $percentile,
                    'performance_level' => $percentile >= 80 ? 'Top Performer' : 
                                         ($percentile >= 60 ? 'Above Average' : 
                                         ($percentile >= 40 ? 'Average' : 
                                         ($percentile >= 20 ? 'Below Average' : 'Needs Improvement'))),
                    'top_products' => array_slice($productStats, 0, 3)
                ];
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Error comparing product: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get profitability level
     */
    private function getProfitabilityLevel($contributionMarginRatio)
    {
        if ($contributionMarginRatio >= 50) return 'Sangat Menguntungkan';
        if ($contributionMarginRatio >= 30) return 'Menguntungkan';
        if ($contributionMarginRatio >= 20) return 'Cukup Menguntungkan';
        if ($contributionMarginRatio >= 10) return 'Kurang Menguntungkan';
        return 'Tidak Menguntungkan';
    }

    /**
     * Get product recommendation
     */
    private function getProductRecommendation($contributionMarginRatio)
    {
        if ($contributionMarginRatio >= 50) {
            return 'Produk ini sangat menguntungkan. Pertimbangkan untuk meningkatkan produksi dan pemasaran.';
        } elseif ($contributionMarginRatio >= 30) {
            return 'Produk ini menguntungkan. Pertahankan kualitas dan pertimbangkan ekspansi.';
        } elseif ($contributionMarginRatio >= 20) {
            return 'Produk ini cukup menguntungkan. Evaluasi biaya produksi untuk meningkatkan margin.';
        } elseif ($contributionMarginRatio >= 10) {
            return 'Produk ini kurang menguntungkan. Review harga jual atau biaya produksi.';
        } else {
            return 'Produk ini tidak menguntungkan. Pertimbangkan untuk menghentikan atau merevisi strategi.';
        }
    }

    /**
     * Analyze custom scenarios
     */
    private function analyzeCustomScenarios()
    {
        $scenarios = [];

        // Skenario Optimis
        $optimisticPrice = $this->customSellingPrice * 1.1; // +10%
        $optimisticVariableCost = $this->customVariableCost * 0.95; // -5%
        $optimisticContribution = $optimisticPrice - $optimisticVariableCost;
        $optimisticBepUnits = $optimisticContribution > 0 ? ceil($this->customFixedCost / $optimisticContribution) : 0;

        $scenarios['optimistic'] = [
            'name' => 'Skenario Optimis',
            'description' => 'Harga naik 10%, biaya variabel turun 5%',
            'selling_price' => $optimisticPrice,
            'variable_cost' => $optimisticVariableCost,
            'contribution_margin' => $optimisticContribution,
            'bep_units' => $optimisticBepUnits,
            'bep_revenue' => $optimisticBepUnits * $optimisticPrice,
            'improvement' => $optimisticBepUnits > 0 ? (($this->customFixedCost / ($this->customSellingPrice - $this->customVariableCost)) - $optimisticBepUnits) : 0
        ];

        // Skenario Pesimis
        $pessimisticPrice = $this->customSellingPrice * 0.95; // -5%
        $pessimisticVariableCost = $this->customVariableCost * 1.1; // +10%
        $pessimisticContribution = $pessimisticPrice - $pessimisticVariableCost;
        $pessimisticBepUnits = $pessimisticContribution > 0 ? ceil($this->customFixedCost / $pessimisticContribution) : 0;

        $scenarios['pessimistic'] = [
            'name' => 'Skenario Pesimis',
            'description' => 'Harga turun 5%, biaya variabel naik 10%',
            'selling_price' => $pessimisticPrice,
            'variable_cost' => $pessimisticVariableCost,
            'contribution_margin' => $pessimisticContribution,
            'bep_units' => $pessimisticBepUnits,
            'bep_revenue' => $pessimisticBepUnits * $pessimisticPrice,
            'deterioration' => $pessimisticBepUnits > 0 ? ($pessimisticBepUnits - ($this->customFixedCost / ($this->customSellingPrice - $this->customVariableCost))) : 0
        ];

        // Skenario Cost Optimization
        $optimizedVariableCost = $this->customVariableCost * 0.8; // -20%
        $optimizedContribution = $this->customSellingPrice - $optimizedVariableCost;
        $optimizedBepUnits = $optimizedContribution > 0 ? ceil($this->customFixedCost / $optimizedContribution) : 0;

        $scenarios['cost_optimization'] = [
            'name' => 'Optimasi Biaya',
            'description' => 'Biaya variabel turun 20%',
            'selling_price' => $this->customSellingPrice,
            'variable_cost' => $optimizedVariableCost,
            'contribution_margin' => $optimizedContribution,
            'bep_units' => $optimizedBepUnits,
            'bep_revenue' => $optimizedBepUnits * $this->customSellingPrice,
            'improvement' => $optimizedBepUnits > 0 ? (($this->customFixedCost / ($this->customSellingPrice - $this->customVariableCost)) - $optimizedBepUnits) : 0
        ];

        return $scenarios;
    }

    /**
     * Perform sensitivity analysis
     */
    private function performSensitivityAnalysis()
    {
        $baseBepUnits = $this->customFixedCost / ($this->customSellingPrice - $this->customVariableCost);
        $sensitivity = [];

        // Sensitivity to price changes
        $priceChanges = [-10, -5, 0, 5, 10, 15, 20];
        foreach ($priceChanges as $change) {
            $newPrice = $this->customSellingPrice * (1 + $change / 100);
            $newContribution = $newPrice - $this->customVariableCost;
            $newBepUnits = $newContribution > 0 ? ceil($this->customFixedCost / $newContribution) : 0;
            
            $sensitivity['price'][$change] = [
                'price' => $newPrice,
                'bep_units' => $newBepUnits,
                'change_percent' => $baseBepUnits > 0 ? (($newBepUnits - $baseBepUnits) / $baseBepUnits) * 100 : 0
            ];
        }

        // Sensitivity to variable cost changes
        $costChanges = [-20, -10, -5, 0, 5, 10, 15];
        foreach ($costChanges as $change) {
            $newCost = $this->customVariableCost * (1 + $change / 100);
            $newContribution = $this->customSellingPrice - $newCost;
            $newBepUnits = $newContribution > 0 ? ceil($this->customFixedCost / $newContribution) : 0;
            
            $sensitivity['variable_cost'][$change] = [
                'variable_cost' => $newCost,
                'bep_units' => $newBepUnits,
                'change_percent' => $baseBepUnits > 0 ? (($newBepUnits - $baseBepUnits) / $baseBepUnits) * 100 : 0
            ];
        }

        // Sensitivity to fixed cost changes
        $fixedCostChanges = [-20, -10, -5, 0, 5, 10, 20];
        foreach ($fixedCostChanges as $change) {
            $newFixedCost = $this->customFixedCost * (1 + $change / 100);
            $newBepUnits = ceil($newFixedCost / ($this->customSellingPrice - $this->customVariableCost));
            
            $sensitivity['fixed_cost'][$change] = [
                'fixed_cost' => $newFixedCost,
                'bep_units' => $newBepUnits,
                'change_percent' => $baseBepUnits > 0 ? (($newBepUnits - $baseBepUnits) / $baseBepUnits) * 100 : 0
            ];
        }

        return $sensitivity;
    }

    /**
     * Generate What-If scenarios
     */
    private function generateWhatIfScenarios()
    {
        $scenarios = [];

        // What if we increase price by 5% and reduce variable cost by 10%
        $scenario1Price = $this->customSellingPrice * 1.05;
        $scenario1Cost = $this->customVariableCost * 0.9;
        $scenario1Contribution = $scenario1Price - $scenario1Cost;
        $scenario1BepUnits = $scenario1Contribution > 0 ? ceil($this->customFixedCost / $scenario1Contribution) : 0;

        $scenarios[] = [
            'name' => 'Harga +5%, Biaya -10%',
            'description' => 'Menaikkan harga jual 5% dan menurunkan biaya variabel 10%',
            'selling_price' => $scenario1Price,
            'variable_cost' => $scenario1Cost,
            'contribution_margin' => $scenario1Contribution,
            'bep_units' => $scenario1BepUnits,
            'bep_revenue' => $scenario1BepUnits * $scenario1Price,
            'impact' => 'Mengurangi BEP unit secara signifikan'
        ];

        // What if we reduce fixed cost by 15%
        $scenario2FixedCost = $this->customFixedCost * 0.85;
        $scenario2BepUnits = ceil($scenario2FixedCost / ($this->customSellingPrice - $this->customVariableCost));

        $scenarios[] = [
            'name' => 'Biaya Tetap -15%',
            'description' => 'Menurunkan biaya tetap 15%',
            'fixed_cost' => $scenario2FixedCost,
            'bep_units' => $scenario2BepUnits,
            'bep_revenue' => $scenario2BepUnits * $this->customSellingPrice,
            'impact' => 'Mengurangi BEP unit dengan mengurangi overhead'
        ];

        // What if we increase volume by 20% (economies of scale)
        $scenario3VariableCost = $this->customVariableCost * 0.9; // 10% reduction due to economies of scale
        $scenario3Contribution = $this->customSellingPrice - $scenario3VariableCost;
        $scenario3BepUnits = $scenario3Contribution > 0 ? ceil($this->customFixedCost / $scenario3Contribution) : 0;

        $scenarios[] = [
            'name' => 'Volume +20% (Economies of Scale)',
            'description' => 'Meningkatkan volume produksi 20% dengan efisiensi biaya 10%',
            'variable_cost' => $scenario3VariableCost,
            'contribution_margin' => $scenario3Contribution,
            'bep_units' => $scenario3BepUnits,
            'bep_revenue' => $scenario3BepUnits * $this->customSellingPrice,
            'impact' => 'Mengurangi BEP melalui economies of scale'
        ];

        return $scenarios;
    }

    /**
     * Helper methods untuk analisis
     */
    private function getPracticalMeaning($result)
    {
        $currentUnits = $result['units_sold'] ?? 0;
        $bepUnits = $result['bep_units'] ?? 0;

        if ($currentUnits >= $bepUnits) {
            $excess = $currentUnits - $bepUnits;
            return "Bagus! Anda sudah melampaui BEP dengan " . number_format($excess, 0, ',', '.') . " unit. Setiap unit tambahan adalah keuntungan murni.";
        } else {
            $shortage = $bepUnits - $currentUnits;
            return "Anda masih perlu menjual " . number_format($shortage, 0, ',', '.') . " unit lagi untuk mencapai titik impas.";
        }
    }

    private function getDailyTarget($bepUnits, $bepRevenue)
    {
        return [
            'units' => number_format($bepUnits / 30, 1, ',', '.') . ' unit/hari',
            'revenue' => 'Rp ' . number_format($bepRevenue / 30, 0, ',', '.') . '/hari'
        ];
    }

    private function getWeeklyTarget($bepUnits, $bepRevenue)
    {
        return [
            'units' => number_format($bepUnits / 4, 1, ',', '.') . ' unit/minggu',
            'revenue' => 'Rp ' . number_format($bepRevenue / 4, 0, ',', '.') . '/minggu'
        ];
    }

    private function calculateOperatingLeverage($result)
    {
        $contributionMargin = $result['contribution_margin'] ?? 0;
        $totalContribution = $contributionMargin * ($result['units_sold'] ?? 0);
        $operatingIncome = $totalContribution - ($result['fixed_cost'] ?? 0);
        
        return $operatingIncome > 0 ? $totalContribution / $operatingIncome : 0;
    }

    private function calculateRiskScore($result)
    {
        $score = 0;
        
        // Margin of Safety (40% weight)
        $marginOfSafety = $result['margin_of_safety_percentage'] ?? 0;
        if ($marginOfSafety >= 30) $score += 40;
        elseif ($marginOfSafety >= 20) $score += 30;
        elseif ($marginOfSafety >= 10) $score += 20;
        else $score += 10;
        
        // Contribution Margin Ratio (35% weight)
        $contributionMarginRatio = ($result['contribution_margin'] ?? 0) / ($result['selling_price_per_unit'] ?? 1) * 100;
        if ($contributionMarginRatio >= 50) $score += 35;
        elseif ($contributionMarginRatio >= 30) $score += 25;
        elseif ($contributionMarginRatio >= 20) $score += 15;
        else $score += 5;
        
        // Profitability (25% weight)
        $profit = $result['profit_loss'] ?? 0;
        if ($profit > 0) $score += 25;
        elseif ($profit >= -($result['fixed_cost'] ?? 0) * 0.1) $score += 15;
        else $score += 5;
        
        return $score;
    }

    private function getRiskMitigationStrategies($overallRisk, $riskFactors)
    {
        $strategies = [];
        
        if ($overallRisk === 'high') {
            $strategies[] = 'Fokus pada peningkatan penjualan dengan strategi marketing yang agresif';
            $strategies[] = 'Review dan potong biaya operasional yang tidak esensial';
            $strategies[] = 'Pertimbangkan diversifikasi produk atau pasar';
            $strategies[] = 'Bangun cadangan kas untuk menghadapi fluktuasi penjualan';
        } elseif ($overallRisk === 'medium') {
            $strategies[] = 'Monitor KPI penjualan secara rutin';
            $strategies[] = 'Optimalkan efisiensi operasional';
            $strategies[] = 'Kembangkan strategi customer retention';
            $strategies[] = 'Evaluasi struktur biaya secara berkala';
        } else {
            $strategies[] = 'Pertahankan kinerja saat ini';
            $strategies[] = 'Pertimbangkan ekspansi atau investasi baru';
            $strategies[] = 'Tingkatkan kapasitas produksi jika ada permintaan';
        }
        
        return $strategies;
    }

    public function getMonthNamesProperty()
    {
        return [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
    }

    public function render()
    {
        return view('livewire.analysis.bep-calculator');
    }
}
