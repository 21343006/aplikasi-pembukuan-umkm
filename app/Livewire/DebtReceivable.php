<?php

namespace App\Livewire;

use App\Models\Debt;
use App\Models\Receivable;
use Livewire\Component;

class DebtReceivable extends Component
{
    public $activeTab = 'debts';

    public function render()
    {
        $totalDebts = Debt::where('user_id', auth()->id())->sum('amount');
        $totalDebtsPaid = Debt::where('user_id', auth()->id())->sum('paid_amount');
        $totalDebtsRemaining = $totalDebts - $totalDebtsPaid;
        $overdueDebts = Debt::where('user_id', auth()->id())
            ->where('due_date', '<', now())
            ->where('status', '!=', 'paid')
            ->count();

        $totalReceivables = Receivable::where('user_id', auth()->id())->sum('amount');
        $totalReceivablesPaid = Receivable::where('user_id', auth()->id())->sum('paid_amount');
        $totalReceivablesRemaining = $totalReceivables - $totalReceivablesPaid;
        $overdueReceivables = Receivable::where('user_id', auth()->id())
            ->where('due_date', '<', now())
            ->where('status', '!=', 'paid')
            ->count();

        return view('livewire.debt-receivable', compact(
            'totalDebts', 'totalDebtsPaid', 'totalDebtsRemaining', 'overdueDebts',
            'totalReceivables', 'totalReceivablesPaid', 'totalReceivablesRemaining', 'overdueReceivables'
        ));
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }
}
