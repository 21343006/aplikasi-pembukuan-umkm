<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Income;
use App\Models\Expenditure;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Reporttahunan extends Component
{
    public $tahun;
    public $laporan = [];
    public $yearlySummary = [];
    public $comparisonData = [];
    public $stats = [];
    public $chartData = [];

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
        $monthlyData = [];

        // 1. Generate monthly report
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $total_income = Income::whereYear('tanggal', $this->tahun)
                ->whereMonth('tanggal', $bulan)
                ->sum(DB::raw('jumlah_terjual * harga_satuan'));

            $total_expenditure = Expenditure::whereYear('tanggal', $this->tahun)
                ->whereMonth('tanggal', $bulan)
                ->sum('jumlah');

            $laba = $total_income - $total_expenditure;

            $monthlyData[$bulan] = [
                'bulan' => Carbon::create()->month($bulan)->translatedFormat('F'),
                'pendapatan' => $total_income,
                'pengeluaran' => $total_expenditure,
                'laba' => $laba,
            ];
        }
        $this->laporan = $monthlyData;

        // 2. Yearly Summary
        $totalPendapatan = array_sum(array_column($this->laporan, 'pendapatan'));
        $totalPengeluaran = array_sum(array_column($this->laporan, 'pengeluaran'));
        $totalLaba = $totalPendapatan - $totalPengeluaran;

        $this->yearlySummary = [
            'pendapatan' => $totalPendapatan,
            'pengeluaran' => $totalPengeluaran,
            'laba' => $totalLaba,
        ];

        // 3. Comparison with Previous Year
        $tahunSebelumnya = $this->tahun - 1;
        $pendapatanSebelumnya = Income::whereYear('tanggal', $tahunSebelumnya)->sum(DB::raw('jumlah_terjual * harga_satuan'));
        $pengeluaranSebelumnya = Expenditure::whereYear('tanggal', $tahunSebelumnya)->sum('jumlah');
        $labaSebelumnya = $pendapatanSebelumnya - $pengeluaranSebelumnya;

        $this->comparisonData = [
            'current_year' => [
                'tahun' => $this->tahun,
                'pendapatan' => $totalPendapatan,
                'pengeluaran' => $totalPengeluaran,
                'laba' => $totalLaba,
            ],
            'previous_year' => [
                'tahun' => $tahunSebelumnya,
                'pendapatan' => $pendapatanSebelumnya,
                'pengeluaran' => $pengeluaranSebelumnya,
                'laba' => $labaSebelumnya,
            ],
        ];

        // 4. Additional Stats
        $labaPerBulan = array_column($this->laporan, 'laba');
        $bulanLabaTertinggi = !empty($labaPerBulan) ? (array_keys($labaPerBulan, max($labaPerBulan))[0] + 1) : null;

        $this->stats = [
            'rata_rata_pendapatan' => $totalPendapatan > 0 ? $totalPendapatan / 12 : 0,
            'bulan_laba_tertinggi' => $bulanLabaTertinggi ? Carbon::create()->month($bulanLabaTertinggi)->translatedFormat('F') : 'N/A',
            'laba_tertinggi' => !empty($labaPerBulan) ? max($labaPerBulan) : 0,
        ];
        
        // 5. Chart Data
        $this->chartData = [
            'categories' => array_values(array_column($this->laporan, 'bulan')),
            'pendapatan' => array_values(array_column($this->laporan, 'pendapatan')),
            'pengeluaran' => array_values(array_column($this->laporan, 'pengeluaran')),
            'laba' => array_values(array_column($this->laporan, 'laba')),
        ];

        Log::info('Chart Data:', $this->chartData);
        Log::info('Comparison Data:', $this->comparisonData);

        $this->dispatch('reportUpdated', ['chartData' => $this->chartData, 'comparisonData' => $this->comparisonData]);
    }

    public function render()
    {
        return view('livewire.reports.reporttahunan-list');
    }
}
