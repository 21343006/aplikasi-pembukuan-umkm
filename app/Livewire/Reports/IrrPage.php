<?php

namespace App\Livewire\Reports;

use App\Models\Capitalearly;
use App\Models\FixedCost;
use App\Models\Income;
use App\Models\Expenditure;
use Livewire\Component;
use Livewire\Attributes\Title;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class IrrPage extends Component
{
    #[Title('Internal Rate of Return (IRR)')]
    
    public $filterYear = '';
    public $cashFlows = [];
    public $yearlyCashFlows = []; // Add yearly cash flows for projection
    public $irr = null;
    public $npv = null;
    public $paybackPeriod = null;
    public $profitabilityIndex = null;
    public $discountRate = 12; // Default discount rate 12%
    public $showCalculation = false;
    public $modalAwal = 0;
    public $monthlyData = [];
    public $projectionYears = 5; // Number of years for projection
    
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

            // Get modal awal (initial investment)
            $this->modalAwal = Capitalearly::sum('modal_awal') ?? 0;

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
                    return $income->jumlah_terjual * $income->harga_satuan;
                });

            // Get expenditures for the month
            $monthlyExpenditure = Expenditure::whereMonth('tanggal', $month)
                ->whereYear('tanggal', $this->filterYear)
                ->sum('jumlah') ?? 0;

            // Get fixed costs for the month
            $monthlyFixedCost = FixedCost::byMonth($month, $this->filterYear)
                ->sum('nominal') ?? 0;

            // Calculate net cash flow
            $netCashFlow = $monthlyIncome - $monthlyExpenditure - $monthlyFixedCost;

            $this->monthlyData[$month] = [
                'month' => $month,
                'month_name' => $this->monthNames[$month],
                'income' => $monthlyIncome,
                'expenditure' => $monthlyExpenditure,
                'fixed_cost' => $monthlyFixedCost,
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
        
        // For demonstration purposes, project future years based on Year 1
        // In real implementation, you might want to allow user input or use growth rates
        for ($year = 2; $year <= $this->projectionYears; $year++) {
            // You can implement different projection methods here:
            // 1. Same as year 1
            $this->yearlyCashFlows[$year] = $year1CashFlow;
            
            // 2. With growth rate (example: 5% growth)
            // $growthRate = 0.05;
            // $this->yearlyCashFlows[$year] = $year1CashFlow * pow(1 + $growthRate, $year - 1);
            
            // 3. User-defined projections (can be added later)
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

    private function computeIRR($cashFlows, $guess = 0.1, $tolerance = 0.0001, $maxIterations = 1000)
    {
        $rate = $guess;
        
        for ($i = 0; $i < $maxIterations; $i++) {
            $npv = 0;
            $npvDerivative = 0;
            
            foreach ($cashFlows as $period => $cashFlow) {
                if ($period == 0) {
                    $npv += $cashFlow;
                } else {
                    $discountFactor = pow(1 + $rate, $period);
                    $npv += $cashFlow / $discountFactor;
                    $npvDerivative -= ($period * $cashFlow) / pow(1 + $rate, $period + 1);
                }
            }
            
            if (abs($npv) < $tolerance) {
                return $rate * 100; // Return as percentage
            }
            
            if ($npvDerivative == 0) {
                break;
            }
            
            $rate = $rate - ($npv / $npvDerivative);
            
            // Prevent extreme values
            if ($rate < -0.99) $rate = -0.99;
            if ($rate > 10) $rate = 10;
        }
        
        return null; // IRR not found
    }

    private function calculateNPV()
    {
        try {
            $this->npv = 0;
            $discountRate = $this->discountRate / 100;
            
            // Use yearly cash flows for NPV calculation
            foreach ($this->yearlyCashFlows as $period => $cashFlow) {
                if ($period == 0) {
                    $this->npv += $cashFlow;
                } else {
                    $this->npv += $cashFlow / pow(1 + $discountRate, $period);
                }
            }
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
            
            // Use yearly cash flows for payback period calculation
            foreach ($this->yearlyCashFlows as $period => $cashFlow) {
                $cumulativeCashFlow += $cashFlow;
                
                if ($cumulativeCashFlow > 0 && $period > 0) {
                    // Calculate exact payback period with interpolation
                    $previousCumulative = $cumulativeCashFlow - $cashFlow;
                    $this->paybackPeriod = ($period - 1) + (abs($previousCumulative) / $cashFlow);
                    $this->paybackPeriod *= 12; // Convert to months for display
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

    public function toggleCalculation()
    {
        $this->showCalculation = !$this->showCalculation;
    }

    public function exportData()
    {
        // Logic for exporting IRR calculation data
        // This can be implemented based on your requirements
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
            return ['status' => 'error', 'message' => 'Tidak dapat dihitung', 'class' => 'text-red-600'];
        }
        
        if ($this->irr > $this->discountRate) {
            return ['status' => 'good', 'message' => 'Investasi Menguntungkan', 'class' => 'text-green-600'];
        } elseif ($this->irr > 0) {
            return ['status' => 'moderate', 'message' => 'Investasi Cukup Baik', 'class' => 'text-yellow-600'];
        } else {
            return ['status' => 'poor', 'message' => 'Investasi Tidak Menguntungkan', 'class' => 'text-red-600'];
        }
    }

    public function getNpvStatus()
    {
        if ($this->npv === null) {
            return ['status' => 'error', 'message' => 'Tidak dapat dihitung', 'class' => 'text-red-600'];
        }
        
        if ($this->npv > 0) {
            return ['status' => 'good', 'message' => 'Positif (Menguntungkan)', 'class' => 'text-green-600'];
        } else {
            return ['status' => 'poor', 'message' => 'Negatif (Tidak Menguntungkan)', 'class' => 'text-red-600'];
        }
    }

    public function render()
    {
        return view('livewire.reports.irr-page');
    }
}