<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Income;
use App\Models\Expenditure;
use App\Models\FixedCost;
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

            // Pengeluaran dari Expenditure (biaya variabel)
            $total_expenditure = Expenditure::whereYear('tanggal', $this->tahun)
                ->whereMonth('tanggal', $bulan)
                ->sum('jumlah');

            // Tambahkan biaya tetap (FixedCost) ke pengeluaran
            $total_fixed_cost = FixedCost::whereYear('tanggal', $this->tahun)
                ->whereMonth('tanggal', $bulan)
                ->sum('nominal');

            // Total pengeluaran = biaya variabel + biaya tetap
            $total_pengeluaran = $total_expenditure + $total_fixed_cost;

            $laba = $total_income - $total_pengeluaran;

            $monthlyData[$bulan] = [
                'bulan' => Carbon::create()->month($bulan)->translatedFormat('F'),
                'pendapatan' => $total_income,
                'pengeluaran_variabel' => $total_expenditure,
                'pengeluaran_tetap' => $total_fixed_cost,
                'pengeluaran' => $total_pengeluaran,
                'laba' => $laba,
            ];
        }
        $this->laporan = $monthlyData;

        // 2. Yearly Summary
        $totalPendapatan = array_sum(array_column($this->laporan, 'pendapatan'));
        $totalPengeluaranVariabel = array_sum(array_column($this->laporan, 'pengeluaran_variabel'));
        $totalPengeluaranTetap = array_sum(array_column($this->laporan, 'pengeluaran_tetap'));
        $totalPengeluaran = array_sum(array_column($this->laporan, 'pengeluaran'));
        $totalLaba = $totalPendapatan - $totalPengeluaran;

        $this->yearlySummary = [
            'pendapatan' => $totalPendapatan,
            'pengeluaran_variabel' => $totalPengeluaranVariabel,
            'pengeluaran_tetap' => $totalPengeluaranTetap,
            'pengeluaran' => $totalPengeluaran,
            'laba' => $totalLaba,
        ];

        // 3. Comparison with Previous Year
        $tahunSebelumnya = $this->tahun - 1;
        $pendapatanSebelumnya = Income::whereYear('tanggal', $tahunSebelumnya)->sum(DB::raw('jumlah_terjual * harga_satuan'));
        $pengeluaranVariabelSebelumnya = Expenditure::whereYear('tanggal', $tahunSebelumnya)->sum('jumlah');
        $pengeluaranTetapSebelumnya = FixedCost::whereYear('tanggal', $tahunSebelumnya)->sum('nominal');
        $pengeluaranSebelumnya = $pengeluaranVariabelSebelumnya + $pengeluaranTetapSebelumnya;
        $labaSebelumnya = $pendapatanSebelumnya - $pengeluaranSebelumnya;

        $this->comparisonData = [
            'current_year' => [
                'tahun' => $this->tahun,
                'pendapatan' => $totalPendapatan,
                'pengeluaran_variabel' => $totalPengeluaranVariabel,
                'pengeluaran_tetap' => $totalPengeluaranTetap,
                'pengeluaran' => $totalPengeluaran,
                'laba' => $totalLaba,
            ],
            'previous_year' => [
                'tahun' => $tahunSebelumnya,
                'pendapatan' => $pendapatanSebelumnya,
                'pengeluaran_variabel' => $pengeluaranVariabelSebelumnya,
                'pengeluaran_tetap' => $pengeluaranTetapSebelumnya,
                'pengeluaran' => $pengeluaranSebelumnya,
                'laba' => $labaSebelumnya,
            ],
        ];

        // 4. Additional Stats
        $labaPerBulan = array_column($this->laporan, 'laba');
        $bulanLabaTertinggi = !empty($labaPerBulan) ? (array_keys($labaPerBulan, max($labaPerBulan))[0] + 1) : null;

        $this->stats = [
            'rata_rata_pendapatan' => $totalPendapatan > 0 ? $totalPendapatan / 12 : 0,
            'rata_rata_pengeluaran_variabel' => $totalPengeluaranVariabel > 0 ? $totalPengeluaranVariabel / 12 : 0,
            'rata_rata_pengeluaran_tetap' => $totalPengeluaranTetap > 0 ? $totalPengeluaranTetap / 12 : 0,
            'bulan_laba_tertinggi' => $bulanLabaTertinggi ? Carbon::create()->month($bulanLabaTertinggi)->translatedFormat('F') : 'N/A',
            'laba_tertinggi' => !empty($labaPerBulan) ? max($labaPerBulan) : 0,
        ];
        
        // 5. Chart Data
        $this->chartData = [
            'categories' => array_values(array_column($this->laporan, 'bulan')),
            'pendapatan' => array_values(array_column($this->laporan, 'pendapatan')),
            'pengeluaran_variabel' => array_values(array_column($this->laporan, 'pengeluaran_variabel')),
            'pengeluaran_tetap' => array_values(array_column($this->laporan, 'pengeluaran_tetap')),
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
