<?php

namespace App\Livewire\Reports;

use App\Models\Capitalearly;
use App\Models\FixedCost;
use App\Models\Income;
use App\Models\Expenditure;
use App\Models\Capital;
use Livewire\Component;
use Livewire\Attributes\Title;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class IrrPage extends Component
{
    #[Title('Internal Rate of Return (IRR)')]
    
    public $filterYear = '';
    public $cashFlows = [];
    public $yearlyCashFlows = [];
    public $irr = null;
    public $npv = null;
    public $paybackPeriod = null;
    public $profitabilityIndex = null;
    public $discountRate = 12; // Default discount rate 12%
    public $showCalculation = false;
    public $modalAwal = 0;
    public $monthlyData = [];
    public $projectionYears = 5;
    public $growthRate = 5; // Default growth rate for projections
    public $inflationRate = 3; // Default inflation rate
    public $riskFreeRate = 6; // Default risk-free rate
    public $riskPremium = 6; // Default risk premium
    
    // Array nama bulan dalam bahasa Indonesia
    public array $monthNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];

    public function mount()
    {
        $this->filterYear = now()->year;
        $this->loadIrrCalculation();
    }

    public function loadIrrCalculation()
    {
        try {
            if (!$this->filterYear) {
                $this->resetCalculation();
                return;
            }

            // Calculate discount rate based on risk-free rate + risk premium
            $this->discountRate = $this->riskFreeRate + $this->riskPremium;

            // Get total initial investment (modal awal + additional capital)
            $this->modalAwal = $this->getTotalInitialInvestment();

            // Calculate monthly cash flows
            $this->calculateMonthlyCashFlows();

            // Calculate yearly cash flows for projection
            $this->calculateYearlyCashFlows();

            // Calculate financial metrics
            $this->calculateIRR();
            $this->calculateNPV();
            $this->calculatePaybackPeriod();
            $this->calculateProfitabilityIndex();

        } catch (\Exception $e) {
            $this->resetCalculation();
            session()->flash('error', 'Terjadi kesalahan saat menghitung IRR: ' . $e->getMessage());
            Log::error('Error in IRR calculation: ' . $e->getMessage());
        }
    }

    private function getTotalInitialInvestment()
    {
        // Get modal awal (hanya dari Capitalearly)
        $modalAwal = Capitalearly::sum('modal_awal') ?? 0;
        
        // Get additional capital investments (hanya modal tambahan, bukan modal awal)
        $additionalCapital = Capital::whereYear('tanggal', $this->filterYear)
            ->where('jenis', 'masuk')
            ->where('keperluan', '!=', 'Modal Awal') // Exclude modal awal
            ->sum('nominal') ?? 0;
        
        return $modalAwal + $additionalCapital;
    }

    private function calculateMonthlyCashFlows()
    {
        $this->monthlyData = [];
        $this->cashFlows = [];

        // Initial investment (negative cash flow)
        $this->cashFlows[0] = -$this->modalAwal;

        for ($month = 1; $month <= 12; $month++) {
            // Get income for the month
            $monthlyIncome = Income::whereMonth('tanggal', $month)
                ->whereYear('tanggal', $this->filterYear)
                ->get()
                ->sum(function ($income) {
                    return $income->total_pendapatan ?? ($income->jumlah_terjual * $income->harga_satuan);
                });

            // Get expenditures for the month
            $monthlyExpenditure = Expenditure::whereMonth('tanggal', $month)
                ->whereYear('tanggal', $this->filterYear)
                ->sum('jumlah') ?? 0;

            // Get fixed costs for the month
            $monthlyFixedCost = FixedCost::whereMonth('tanggal', $month)
                ->whereYear('tanggal', $this->filterYear)
                ->sum('nominal') ?? 0;

            // Get additional capital for the month (hanya modal tambahan, bukan modal awal)
            $monthlyCapital = Capital::whereMonth('tanggal', $month)
                ->whereYear('tanggal', $this->filterYear)
                ->where('jenis', 'masuk')
                ->where('keperluan', '!=', 'Modal Awal') // Exclude modal awal
                ->sum('nominal') ?? 0;

            // Calculate net cash flow
            $netCashFlow = $monthlyIncome - $monthlyExpenditure - $monthlyFixedCost - $monthlyCapital;

            $this->monthlyData[$month] = [
                'month' => $month,
                'month_name' => $this->monthNames[$month],
                'income' => $monthlyIncome,
                'expenditure' => $monthlyExpenditure,
                'fixed_cost' => $monthlyFixedCost,
                'capital' => $monthlyCapital,
                'net_cash_flow' => $netCashFlow
            ];

            $this->cashFlows[$month] = $netCashFlow;
        }
    }

    private function calculateYearlyCashFlows()
    {
        $this->yearlyCashFlows = [];
        
        // Year 0: Initial Investment (negative)
        $this->yearlyCashFlows[0] = -$this->modalAwal;
        
        // Year 1: Sum of all monthly cash flows
        $year1CashFlow = $this->getTotalNetCashFlow();
        $this->yearlyCashFlows[1] = $year1CashFlow;
        
        // Project future years with growth rate and inflation adjustments
        for ($year = 2; $year <= $this->projectionYears; $year++) {
            // Apply growth rate and inflation adjustments
            $growthFactor = pow(1 + ($this->growthRate / 100), $year - 1);
            $inflationFactor = pow(1 + ($this->inflationRate / 100), $year - 1);
            
            // Net effect: growth - inflation
            $netFactor = $growthFactor / $inflationFactor;
            $this->yearlyCashFlows[$year] = $year1CashFlow * $netFactor;
        }
    }

    private function calculateIRR()
    {
        try {
            // Use yearly cash flows for IRR calculation
            $this->irr = $this->computeIRR($this->yearlyCashFlows);
        } catch (\Exception $e) {
            $this->irr = null;
            Log::error('Error calculating IRR: ' . $e->getMessage());
        }
    }

    /**
     * Improved IRR calculation using Newton-Raphson method
     */
    private function computeIRR(array $cashFlows, float $guess = 0.1, float $tolerance = 1e-7, int $maxIterations = 100): ?float
    {
        // Validate cash flows
        if (count($cashFlows) < 2) {
            return null;
        }

        // Check if we have both positive and negative cash flows
        $hasPositive = false;
        $hasNegative = false;
        foreach ($cashFlows as $cf) {
            if ($cf > 0) $hasPositive = true;
            if ($cf < 0) $hasNegative = true;
        }
        
        if (!($hasPositive && $hasNegative)) {
            return null;
        }

        // Newton-Raphson method for IRR calculation
        $rate = $guess;
        
        for ($i = 0; $i < $maxIterations; $i++) {
            $npv = $this->calculateNPVAtRate($cashFlows, $rate);
            $derivative = $this->calculateNPVDerivative($cashFlows, $rate);
            
            if (abs($derivative) < $tolerance) {
                break;
            }
            
            $newRate = $rate - $npv / $derivative;
            
            // Prevent extreme values
            if ($newRate < -0.99 || $newRate > 10) {
                break;
            }
            
            if (abs($newRate - $rate) < $tolerance) {
                $rate = $newRate;
                break;
            }
            
            $rate = $newRate;
        }
        
        // Validate the result
        if ($rate < -0.99 || $rate > 10) {
            return null;
        }
        
        return $rate * 100; // Convert to percentage
    }

    private function calculateNPVAtRate(array $cashFlows, float $rate): float
    {
        $npv = 0.0;
        foreach ($cashFlows as $period => $cashFlow) {
            if ($period == 0) {
                $npv += $cashFlow;
            } else {
                $npv += $cashFlow / pow(1 + $rate, $period);
            }
        }
        return $npv;
    }

    private function calculateNPVDerivative(array $cashFlows, float $rate): float
    {
        $derivative = 0.0;
        foreach ($cashFlows as $period => $cashFlow) {
            if ($period > 0) {
                $derivative -= $period * $cashFlow / pow(1 + $rate, $period + 1);
            }
        }
        return $derivative;
    }

    private function calculateNPV()
    {
        try {
            $this->npv = $this->calculateNPVAtRate($this->yearlyCashFlows, $this->discountRate / 100);
        } catch (\Exception $e) {
            $this->npv = null;
            Log::error('Error calculating NPV: ' . $e->getMessage());
        }
    }

    private function calculatePaybackPeriod()
    {
        try {
            $cumulativeCashFlow = 0;
            $this->paybackPeriod = null;
            
            foreach ($this->yearlyCashFlows as $period => $cashFlow) {
                $cumulativeCashFlow += $cashFlow;
                
                if ($cumulativeCashFlow >= 0 && $period > 0) {
                    // Calculate exact payback period with interpolation
                    $previousCumulative = $cumulativeCashFlow - $cashFlow;
                    $fraction = abs($previousCumulative) / $cashFlow;
                    $this->paybackPeriod = ($period - 1) + $fraction;
                    $this->paybackPeriod *= 12; // Convert to months
                    break;
                }
            }
        } catch (\Exception $e) {
            $this->paybackPeriod = null;
            Log::error('Error calculating payback period: ' . $e->getMessage());
        }
    }

    private function calculateProfitabilityIndex()
    {
        try {
            if ($this->modalAwal > 0) {
                $presentValueOfCashInflows = $this->npv + $this->modalAwal;
                $this->profitabilityIndex = $presentValueOfCashInflows / $this->modalAwal;
            } else {
                $this->profitabilityIndex = null;
            }
        } catch (\Exception $e) {
            $this->profitabilityIndex = null;
            Log::error('Error calculating profitability index: ' . $e->getMessage());
        }
    }

    private function resetCalculation()
    {
        $this->cashFlows = [];
        $this->yearlyCashFlows = [];
        $this->monthlyData = [];
        $this->irr = null;
        $this->npv = null;
        $this->paybackPeriod = null;
        $this->profitabilityIndex = null;
        $this->modalAwal = 0;
    }

    public function updatedFilterYear()
    {
        $this->loadIrrCalculation();
    }

    public function updatedDiscountRate()
    {
        $this->calculateNPV();
        $this->calculateProfitabilityIndex();
    }

    public function updatedGrowthRate()
    {
        $this->calculateYearlyCashFlows();
        $this->calculateIRR();
        $this->calculateNPV();
        $this->calculatePaybackPeriod();
        $this->calculateProfitabilityIndex();
    }

    public function updatedInflationRate()
    {
        $this->calculateYearlyCashFlows();
        $this->calculateIRR();
        $this->calculateNPV();
        $this->calculatePaybackPeriod();
        $this->calculateProfitabilityIndex();
    }

    public function toggleCalculation()
    {
        $this->showCalculation = !$this->showCalculation;
    }

    public function exportData()
    {
        // Logic for exporting IRR calculation data
        session()->flash('info', 'Fitur export akan segera tersedia.');
    }

    // Helper methods for view
    public function getTotalIncome()
    {
        return collect($this->monthlyData)->sum('income');
    }

    public function getTotalExpenditure()
    {
        return collect($this->monthlyData)->sum('expenditure');
    }

    public function getTotalFixedCost()
    {
        return collect($this->monthlyData)->sum('fixed_cost');
    }

    public function getTotalCapital()
    {
        return collect($this->monthlyData)->sum('capital');
    }

    public function getTotalNetCashFlow()
    {
        return collect($this->monthlyData)->sum('net_cash_flow');
    }

    public function getYearlyCashFlow($year)
    {
        return $this->yearlyCashFlows[$year] ?? 0;
    }

    public function getIrrStatus()
    {
        if ($this->irr === null) {
            return ['status' => 'error', 'message' => 'Tidak dapat dihitung', 'class' => 'text-danger'];
        }
        
        if ($this->irr > $this->discountRate) {
            return ['status' => 'good', 'message' => 'Investasi Sangat Layak', 'class' => 'text-success'];
        } elseif ($this->irr > 0) {
            return ['status' => 'moderate', 'message' => 'Investasi Cukup Layak', 'class' => 'text-warning'];
        } else {
            return ['status' => 'poor', 'message' => 'Investasi Tidak Layak', 'class' => 'text-danger'];
        }
    }

    public function getNpvStatus()
    {
        if ($this->npv === null) {
            return ['status' => 'error', 'message' => 'Tidak dapat dihitung', 'class' => 'text-danger'];
        }
        
        if ($this->npv > 0) {
            return ['status' => 'good', 'message' => 'Positif (Menguntungkan)', 'class' => 'text-success'];
        } else {
            return ['status' => 'poor', 'message' => 'Negatif (Tidak Menguntungkan)', 'class' => 'text-danger'];
        }
    }

    public function getPaybackStatus()
    {
        if ($this->paybackPeriod === null) {
            return ['status' => 'error', 'message' => 'Tidak dapat dihitung', 'class' => 'text-danger'];
        }
        
        if ($this->paybackPeriod <= 12) {
            return ['status' => 'good', 'message' => 'Sangat Cepat (< 1 tahun)', 'class' => 'text-success'];
        } elseif ($this->paybackPeriod <= 24) {
            return ['status' => 'moderate', 'message' => 'Cukup Cepat (1-2 tahun)', 'class' => 'text-warning'];
        } else {
            return ['status' => 'poor', 'message' => 'Lambat (> 2 tahun)', 'class' => 'text-danger'];
        }
    }

    public function getProfitabilityIndexStatus()
    {
        if ($this->profitabilityIndex === null) {
            return ['status' => 'error', 'message' => 'Tidak dapat dihitung', 'class' => 'text-danger'];
        }
        
        if ($this->profitabilityIndex > 1.5) {
            return ['status' => 'good', 'message' => 'Sangat Menguntungkan', 'class' => 'text-success'];
        } elseif ($this->profitabilityIndex > 1) {
            return ['status' => 'moderate', 'message' => 'Menguntungkan', 'class' => 'text-warning'];
        } else {
            return ['status' => 'poor', 'message' => 'Tidak Menguntungkan', 'class' => 'text-danger'];
        }
    }

    public function render()
    {
        return view('livewire.reports.irr-page');
    }
}