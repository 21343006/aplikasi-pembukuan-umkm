<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Report;
use App\Models\Capitalearly;
use App\Models\Reportharian;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReportharianPage extends Component
{
    #[Title('Laporan Harian')]

    // Properties untuk form create
    public $tanggal_input;
    public $keterangan;
    public $uang_masuk = 0;
    public $uang_keluar = 0;

    // Properties untuk list
    public $tanggal_filter;
    public $reports = [];

    // Properties untuk UI state
    public $showModal = false;
    public $isEdit = false;
    public $editId = null;

    public function mount()
    {
        $this->tanggal_filter = now()->format('Y-m-d');
        $this->tanggal_input = now()->format('Y-m-d');
        $this->loadData();
    }

    public function openModal()
    {
        $this->showModal = true;
        $this->isEdit = false;
        $this->tanggal_input = $this->tanggal_filter;
        $this->reset(['keterangan', 'uang_masuk', 'uang_keluar']);
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->isEdit = false;
        $this->editId = null;
        $this->reset(['tanggal_input', 'keterangan', 'uang_masuk', 'uang_keluar']);
    }

    public function edit($id)
    {
        $report = Reportharian::where('user_id', Auth::id())->findOrFail($id);
        $this->editId = $id;
        $this->isEdit = true;
        $this->tanggal_input = $report->tanggal->format('Y-m-d');
        $this->keterangan = $report->keterangan;
        $this->uang_masuk = $report->uang_masuk;
        $this->uang_keluar = $report->uang_keluar;
        $this->showModal = true;
    }

    public function confirmDelete($id)
    {
        $report = Reportharian::where('user_id', Auth::id())->findOrFail($id);
        $report->delete();

        $this->recalculateAllSaldos();

        session()->flash('message', 'Data berhasil dihapus.');
        $this->loadData();
    }

    public function save()
    {
        $this->validate([
            'tanggal_input' => 'required|date',
            'keterangan' => 'nullable|string',
            'uang_masuk' => 'numeric|min:0',
            'uang_keluar' => 'numeric|min:0',
        ]);

        $tanggalInput = $this->tanggal_input;
        $uangMasuk = floatval($this->uang_masuk ?? 0);
        $uangKeluar = floatval($this->uang_keluar ?? 0);

        if ($this->isEdit) {
            $report = Reportharian::where('user_id', Auth::id())->findOrFail($this->editId);
            $report->update([
                'tanggal' => $tanggalInput,
                'keterangan' => $this->keterangan,
                'uang_masuk' => $uangMasuk,
                'uang_keluar' => $uangKeluar,
            ]);

            session()->flash('message', 'Data berhasil diupdate.');
        } else {
            $saldoSebelumnya = Reportharian::where('user_id', Auth::id())
                ->where('tanggal', '<', $tanggalInput)
                ->orderBy('tanggal', 'desc')
                ->orderBy('id', 'desc')
                ->value('saldo') ?? 0;

            $saldo = $saldoSebelumnya + $uangMasuk - $uangKeluar;

            Reportharian::create([
                'user_id' => Auth::id(),
                'tanggal' => $tanggalInput,
                'keterangan' => $this->keterangan,
                'uang_masuk' => $uangMasuk,
                'uang_keluar' => $uangKeluar,
                'saldo' => $saldo,
            ]);

            if (Carbon::parse($tanggalInput)->day === 1) {
                Reportharian::updateOrCreate(
                    [
                        'user_id' => Auth::id(),
                        'tanggal' => $tanggalInput,
                        'keterangan' => 'Saldo Awal Bulan',
                    ],
                    [
                        'uang_masuk' => 0,
                        'uang_keluar' => 0,
                        'saldo' => $saldo,
                    ]
                );
            }

            session()->flash('message', 'Data berhasil disimpan.');
        }

        $this->recalculateAllSaldos();
        $this->closeModal();
        $this->loadData();
    }

    private function recalculateAllSaldos()
    {
        $allReports = Reportharian::where('user_id', Auth::id())
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        $saldoBerjalan = 0;

        foreach ($allReports as $report) {
            $saldoBerjalan += $report->uang_masuk - $report->uang_keluar;
            $report->update(['saldo' => $saldoBerjalan]);
        }
    }

    public function tanggalChanged()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $tanggal = $this->tanggal_filter;

        $reportData = Reportharian::where('user_id', Auth::id())
            ->where('tanggal', '<=', $tanggal)
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
                    'raw_id' => $item->id,
                ];
            });

        $modalAwalData = Capitalearly::where('user_id', Auth::id())
            ->whereDate('created_at', '<=', $tanggal)
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

        $merged = $reportData->concat($modalAwalData)
            ->sortBy(function ($item) {
                return $item->tanggal . ($item->raw_id ?? '');
            })->values();

        $saldo = 0;
        foreach ($merged as $item) {
            $saldo += $item->uang_masuk - $item->uang_keluar;
            $item->saldo = $saldo;
        }

        $this->reports = $merged->filter(function ($item) use ($tanggal) {
            return Carbon::parse($item->tanggal)->format('Y-m-d') === $tanggal;
        })->values()->toArray();
    }

    public function render()
    {
        return view('livewire.reports.reportharian-page');
    }
}
