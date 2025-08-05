<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Report;
use App\Models\Capitalearly;
use App\Models\Reportharian;
use Livewire\Attributes\Title;

class ReportharianList extends Component
{
    #[Title('Laporan Harian')]

    public $tanggal;
    protected $reports = [];

    public function mount()
    {
        $this->tanggal = now()->format('Y-m-d');
        $this->loadData();
    }

    public function tanggalChanged()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $tanggal = $this->tanggal;

        // Ambil semua data laporan dari tanggal awal sampai tanggal yang dipilih
        $reportData = Reportharian::where('tanggal', '<=', $tanggal)
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get()
            ->map(function ($item) {
                return (object)[
                    'tanggal' => $item->tanggal,
                    'keterangan' => $item->keterangan,
                    'uang_masuk' => $item->uang_masuk,
                    'uang_keluar' => $item->uang_keluar,
                    'jenis' => 'Laporan',
                    'raw_id' => $item->id, // opsional, untuk tracking
                ];
            });

        // Ambil modal awal
        $modalAwalData = Capitalearly::whereDate('created_at', '<=', $tanggal)
            ->get()
            ->map(function ($item) {
                return (object)[
                    'tanggal' => $item->created_at->format('Y-m-d'),
                    'keterangan' => 'Modal Awal',
                    'uang_masuk' => $item->modal_awal,
                    'uang_keluar' => 0,
                    'jenis' => 'Modal',
                ];
            });

        // Gabungkan dan urutkan berdasarkan tanggal
        $merged = $reportData->concat($modalAwalData)
            ->sortBy(function ($item) {
                return $item->tanggal . ($item->raw_id ?? '');
            })->values();

        // Hitung saldo berjalan
        $saldo = 0;
        foreach ($merged as $item) {
            $saldo += $item->uang_masuk - $item->uang_keluar;
            $item->saldo = $saldo;
        }

        // Filter hanya tanggal yang dipilih
        $this->reports = $merged->filter(function ($item) use ($tanggal) {
            return \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d') === $tanggal;
        })->values();
    }




    public function render()
    {
        return view('livewire.reports.reportharian-list', [
            'reports' => $this->reports,
        ]);
    }
}
