<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Reportharian;
use App\Models\Capitalearly;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ReportbulananList extends Component
{
    public $bulan;
    public $tahun;
    public $rekapHarian = [];
    public $saldoAwal = 0;
    public $saldoAkhirBulanIni = 0;
    public $totalSaldoKumulatif = 0;
    public $totalUangMasuk = 0;
    public $totalUangKeluar = 0;

    public function mount()
    {
        $this->bulan = now()->month;
        $this->tahun = now()->year;
        $this->loadRekap();
    }

    public function updatedBulan()
    {
        $this->loadRekap();
    }

    public function updatedTahun()
    {
        $this->loadRekap();
    }

    public function loadRekap()
    {
        $this->rekapHarian = [];
        $this->saldoAwal = 0;
        $this->saldoAkhirBulanIni = 0;
        $this->totalSaldoKumulatif = 0;
        $this->totalUangMasuk = 0;
        $this->totalUangKeluar = 0;

        $bulan = (int) $this->bulan;
        $tahun = (int) $this->tahun;

        if ($bulan < 1 || $bulan > 12 || $tahun < 2020) {
            return;
        }

        try {
            // Hitung saldo kumulatif dari semua data sebelum bulan ini
            $this->saldoAwal = $this->hitungSaldoSebelumBulan($bulan, $tahun);

            // Data bulan ini
            $reportData = Reportharian::whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', $bulan)
                ->orderBy('tanggal')
                ->get();

            $reportCollection = $reportData->map(function ($item) {
                return (object)[
                    'tanggal' => Carbon::parse($item->tanggal)->format('Y-m-d'),
                    'masuk' => (float) $item->uang_masuk,
                    'keluar' => (float) $item->uang_keluar,
                ];
            });

            $modalAwalData = Capitalearly::whereYear('created_at', $tahun)
                ->whereMonth('created_at', $bulan)
                ->orderBy('created_at')
                ->get();

            $modalCollection = $modalAwalData->map(function ($item) {
                return (object)[
                    'tanggal' => Carbon::parse($item->created_at)->format('Y-m-d'),
                    'masuk' => (float) $item->modal_awal,
                    'keluar' => 0,
                ];
            });

            $merged = $reportCollection->concat($modalCollection);

            $grouped = $merged->groupBy('tanggal')->map(function ($items) {
                return [
                    'masuk' => $items->sum('masuk'),
                    'keluar' => $items->sum('keluar'),
                ];
            });

            $this->rekapHarian = $grouped->sortKeys()->toArray();

            // Hitung total uang masuk dan keluar bulan ini
            $this->totalUangMasuk = collect($this->rekapHarian)->sum('masuk');
            $this->totalUangKeluar = collect($this->rekapHarian)->sum('keluar');

            // Hitung saldo akhir bulan ini (hanya transaksi bulan ini)
            $this->saldoAkhirBulanIni = $this->totalUangMasuk - $this->totalUangKeluar;

            // Total saldo kumulatif = saldo awal + saldo perubahan bulan ini
            $this->totalSaldoKumulatif = $this->saldoAwal + $this->saldoAkhirBulanIni;

        } catch (\Exception $e) {
            Log::error('Gagal memuat rekap bulanan: ' . $e->getMessage());
            $this->saldoAwal = 0;
            $this->saldoAkhirBulanIni = 0;
            $this->totalSaldoKumulatif = 0;
            $this->totalUangMasuk = 0;
            $this->totalUangKeluar = 0;
        }
    }

    /**
     * Hitung total saldo kumulatif dari semua periode sebelum bulan yang dipilih
     */
    private function hitungSaldoSebelumBulan($bulan, $tahun)
    {
        try {
            $totalSaldo = 0;

            // Hitung semua data laporan sebelum bulan ini
            $allReports = Reportharian::where(function ($query) use ($bulan, $tahun) {
                $query->where('tanggal', '<', Carbon::create($tahun, $bulan, 1));
            })->get();

            // Hitung semua modal awal sebelum bulan ini
            $allModal = Capitalearly::where(function ($query) use ($bulan, $tahun) {
                $query->where('created_at', '<', Carbon::create($tahun, $bulan, 1));
            })->get();

            // Total dari laporan harian
            $totalSaldo += $allReports->sum('uang_masuk') - $allReports->sum('uang_keluar');
            
            // Total dari modal awal
            $totalSaldo += $allModal->sum('modal_awal');

            return $totalSaldo;

        } catch (\Exception $e) {
            Log::error('Error menghitung saldo sebelum bulan: ' . $e->getMessage());
            return 0;
        }
    }

    public function render()
    {
        return view('livewire.reports.reportbulanan-list');
    }
}