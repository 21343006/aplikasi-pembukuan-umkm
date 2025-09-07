<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\Income;
use App\Models\Expenditure;
use App\Models\Capitalearly;
use App\Models\Capital;
use App\Models\FixedCost;
use App\Models\Debt;
use App\Models\Receivable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class Dashboard extends Component
{
    #[Title('Dashboard')]
    public $totalIncome;
    public $totalExpenditure;
    public $currentBalance;
    public $totalDailyIncome;
    public $totalDailyExpenditure;

    public function render()
    {
        $userId = Auth::id();
        $today = Carbon::today();

        // Inisialisasi variabel dengan nilai default
        $totalDebts = 0;
        $totalDebtsPaid = 0;
        $totalDebtsRemaining = 0;
        $totalReceivables = 0;
        $totalReceivablesPaid = 0;
        $totalReceivablesRemaining = 0;
        $overdueDebts = 0;
        $overdueReceivables = 0;

        if (!$userId) {
            return view('livewire.dashboard', [
                'totalIncome' => 0,
                'totalExpenditure' => 0,
                'currentBalance' => 0,
                'totalDailyIncome' => 0,
                'totalDailyExpenditure' => 0,
                'totalDebts' => $totalDebts,
                'totalDebtsPaid' => $totalDebtsPaid,
                'totalDebtsRemaining' => $totalDebtsRemaining,
                'totalReceivables' => $totalReceivables,
                'totalReceivablesPaid' => $totalReceivablesPaid,
                'totalReceivablesRemaining' => $totalReceivablesRemaining,
                'overdueDebts' => $overdueDebts,
                'overdueReceivables' => $overdueReceivables,
            ]);
        }

        try {
            // Daily Income for Pie Chart - gunakan model dengan global scope
            $this->totalDailyIncome = Income::where('tanggal', '<=', $today)
                ->sum(DB::raw('jumlah_terjual * harga_satuan'));

            // Daily Expenditure for Pie Chart - gunakan model dengan global scope
            $this->totalDailyExpenditure = Expenditure::where('tanggal', '<=', $today)
                ->sum('jumlah');

            // Income for Saldo Terkini - gunakan model dengan global scope
            $dailyIncomeForBalance = $this->totalDailyIncome;
            $capitalIncome = Capitalearly::where('tanggal_input', '<=', $today)
                ->sum('modal_awal');

            $this->totalIncome = $dailyIncomeForBalance + $capitalIncome;

            // Expenditure for Saldo Terkini - gunakan model dengan global scope
            $dailyExpenditureForBalance = $this->totalDailyExpenditure;
            $capitalExpenditure = 0;
            if (Schema::hasColumn('capitals', 'jenis')) {
                $capitalExpenditure = Capital::where('jenis', 'keluar')
                    ->where('tanggal', '<=', $today)
                    ->sum('nominal');
            }

            $fixedCostExpenditure = FixedCost::where('tanggal', '<=', $today)
                ->sum('nominal');

            $this->totalExpenditure = $dailyExpenditureForBalance + $capitalExpenditure + $fixedCostExpenditure;

            // Hitung saldo terkini
            $this->currentBalance = $this->totalIncome - $this->totalExpenditure;

            // Hitung total utang dan piutang
            $totalDebts = Debt::sum('amount');
            $totalDebtsPaid = Debt::where('paid_amount', '>', 0)->sum('paid_amount');
            $totalDebtsRemaining = $totalDebts - $totalDebtsPaid;

            $totalReceivables = Receivable::sum('amount');
            $totalReceivablesPaid = Receivable::where('paid_amount', '>', 0)->sum('paid_amount');
            $totalReceivablesRemaining = $totalReceivables - $totalReceivablesPaid;

            // Hitung utang dan piutang yang jatuh tempo
            $overdueDebts = Debt::where('due_date', '<', $today)
                ->where('status', '!=', 'paid')
                ->count();

            $overdueReceivables = Receivable::where('due_date', '<', $today)
                ->where('status', '!=', 'paid')
                ->count();

        } catch (\Exception $e) {
            // Fallback values jika terjadi error
            $this->totalIncome = 0;
            $this->totalExpenditure = 0;
            $this->currentBalance = 0;
            $this->totalDailyIncome = 0;
            $this->totalDailyExpenditure = 0;
            
            Log::error('Error in Dashboard render: ' . $e->getMessage(), [
                'user_id' => $userId,
                'trace' => $e->getTraceAsString()
            ]);
        }

        return view('livewire.dashboard', [
            'totalIncome' => $this->totalIncome,
            'totalExpenditure' => $this->totalExpenditure,
            'currentBalance' => $this->currentBalance,
            'totalDailyIncome' => $this->totalDailyIncome,
            'totalDailyExpenditure' => $this->totalDailyExpenditure,
            'totalDebts' => $totalDebts,
            'totalDebtsPaid' => $totalDebtsPaid,
            'totalDebtsRemaining' => $totalDebtsRemaining,
            'totalReceivables' => $totalReceivables,
            'totalReceivablesPaid' => $totalReceivablesPaid,
            'totalReceivablesRemaining' => $totalReceivablesRemaining,
            'overdueDebts' => $overdueDebts,
            'overdueReceivables' => $overdueReceivables,
        ]);
    }
}
