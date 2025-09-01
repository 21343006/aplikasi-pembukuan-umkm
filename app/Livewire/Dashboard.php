<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\Income;
use App\Models\Expenditure;
use App\Models\Capitalearly;
use App\Models\Capital;
use App\Models\FixedCost;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

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

        // Daily Income for Pie Chart
        $this->totalDailyIncome = DB::table('incomes')
            ->where('user_id', $userId)
            ->where('tanggal', '<=', $today)
            ->sum(DB::raw('jumlah_terjual * harga_satuan'));

        // Daily Expenditure for Pie Chart
        $this->totalDailyExpenditure = Expenditure::where('user_id', $userId)
            ->where('tanggal', '<=', $today)
            ->sum('jumlah');

        // Income for Saldo Terkini
        $dailyIncomeForBalance = $this->totalDailyIncome;
        $capitalIncome = Capitalearly::where('user_id', $userId)
            ->where('tanggal_input', '<=', $today)
            ->sum('modal_awal');

        $this->totalIncome = $dailyIncomeForBalance + $capitalIncome;

        // Expenditure for Saldo Terkini
        $dailyExpenditureForBalance = $this->totalDailyExpenditure;
        $capitalExpenditure = 0;
        if (Schema::hasColumn('capitals', 'jenis')) {
            $capitalExpenditure = Capital::where('user_id', $userId)
                ->where('jenis', 'keluar')
                ->where('tanggal', '<=', $today)
                ->sum('nominal');
        }

        $fixedCostExpenditure = FixedCost::where('user_id', $userId)
            ->where('tanggal', '<=', $today)
            ->sum('nominal');

        $this->totalExpenditure = $dailyExpenditureForBalance + $capitalExpenditure + $fixedCostExpenditure;

        // Balance
        $this->currentBalance = $this->totalIncome - $this->totalExpenditure;

        return view('livewire.dashboard', [
            'totalIncome' => $this->totalIncome,
            'totalExpenditure' => $this->totalExpenditure,
            'currentBalance' => $this->currentBalance,
            'totalDailyIncome' => $this->totalDailyIncome,
            'totalDailyExpenditure' => $this->totalDailyExpenditure,
        ]);
    }
}
