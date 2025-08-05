<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Income;
use App\Models\Expenditure;

class ProfitLoss extends Component
{
    public function render()
    {
        $pendapatan = Income::all();
        $totalPendapatan = $pendapatan->sum('total');

        $pengeluaran = Expenditure::all();
        $totalPengeluaran = $pengeluaran->sum('jumlah');

        $labaRugi = $totalPendapatan - $totalPengeluaran;

        return view('livewire.reports.profitloss', [
            'pendapatan' => $totalPendapatan,
            'pengeluaran' => $totalPengeluaran,
            'labaRugi' => $labaRugi,
        ]);
    }
}
