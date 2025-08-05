<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Income;
use App\Models\Expenditure;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Reporttahunan extends Component
{
    public $tahun;
    public $laporan = [];

    public function mount()
    {
        $this->tahun = now()->year; // Default: tahun ini
        $this->generateReport();
    }

    public function updatedTahun()
    {
        $this->generateReport();
    }

    public function generateReport()
    {
        $this->laporan = [];

        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $total_income = Income::whereYear('tanggal', $this->tahun)
                ->whereMonth('tanggal', $bulan)
                ->sum(DB::raw('jumlah_terjual * harga_satuan'));

            $total_expenditure = Expenditure::whereYear('tanggal', $this->tahun)
                ->whereMonth('tanggal', $bulan)
                ->sum('jumlah');

            $this->laporan[] = [
                'bulan' => Carbon::create()->month($bulan)->translatedFormat('F'),
                'pendapatan' => $total_income,
                'pengeluaran' => $total_expenditure,
                'laba' => $total_income - $total_expenditure,
            ];
        }
    }

    public function render()
    {
        return view('livewire.reports.reporttahunan-list');
    }
}
