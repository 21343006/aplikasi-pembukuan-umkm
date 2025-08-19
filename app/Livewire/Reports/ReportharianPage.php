<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Capital;
use App\Models\Capitalearly;
use App\Models\Reportharian;
use App\Models\Income;
use App\Models\Expenditure;
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

    protected $rules = [
        'tanggal_input' => 'required|date',
        'keterangan' => 'nullable|string',
    ];

    

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
        // Set tanggal input berdasarkan tanggal filter yang dipilih
        $this->tanggal_input = $this->tanggal_filter;
        $this->reset(['keterangan', 'uang_masuk', 'uang_keluar']);
    }

    public function updatedTanggalFilter($value)
    {
        $this->tanggal_filter = $value;
        $this->tanggal_input = $value; // Update tanggal_input as well
        $this->loadData();
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

        // Calculate daily income and expenditure dynamically
        $daily_uang_masuk = Income::whereDate('tanggal', $tanggal)
                                ->selectRaw('SUM(jumlah_terjual * harga_satuan) as total_income')
                                ->value('total_income') ?? 0;
        $daily_uang_keluar = Expenditure::whereDate('tanggal', $tanggal)->sum('jumlah') ?? 0;
        $dynamicSummary = (object)[
            'tanggal' => Carbon::parse($tanggal)->format('Y-m-d'),
            'keterangan' => 'Ringkasan Harian', // Changed to just "Ringkasan Harian"
            'uang_masuk' => $daily_uang_masuk,
            'uang_keluar' => $daily_uang_keluar,
            'jenis' => 'Laporan', // Changed to "Laporan"
            'raw_id' => null, // No raw ID for dynamic entry
        ];

        // Hitung saldo sampai hari sebelumnya
        $saldoSebelumnya = $this->getSaldoSampaiTanggal(
            Carbon::parse($tanggal)->subDay()->format('Y-m-d')
        );

        // 1. Data laporan harian untuk tanggal yang dipilih
        $reportData = Reportharian::where('user_id', Auth::id())
            ->whereDate('tanggal', $tanggal)
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

        // 2. Data modal awal untuk tanggal yang dipilih
        $modalAwalData = Capitalearly::where('user_id', Auth::id())
            ->whereDate('tanggal_input', $tanggal)
            ->get()
            ->map(function ($item) {
                return (object)[
                    'tanggal' => Carbon::parse($item->tanggal_input)->format('Y-m-d'),
                    'keterangan' => 'Modal Awal',
                    'uang_masuk' => $item->modal_awal,
                    'uang_keluar' => 0,
                    'jenis' => 'Modal',
                ];
            });

        // 3. Data modal keluar untuk tanggal yang dipilih
        $modalKeluarData = collect();
        if (\Schema::hasColumn('capitals', 'jenis')) {
            $modalKeluarData = Capital::where('user_id', Auth::id())
                ->where('jenis', 'keluar')
                ->whereDate('tanggal', $tanggal)
                ->get()
                ->map(function ($item) {
                    return (object)[
                        'tanggal' => Carbon::parse($item->tanggal)->format('Y-m-d'),
                        'keterangan' => $item->keterangan ?? $item->keperluan ?? 'Modal Keluar',
                        'uang_masuk' => 0,
                        'uang_keluar' => $item->nominal,
                        'jenis' => 'Modal Keluar',
                    ];
                });
        }

        // Gabungkan data hari ini, including the dynamic summary
        $merged = collect([$dynamicSummary])->concat($reportData)->concat($modalAwalData)->concat($modalKeluarData)
            ->sortBy(function ($item) {
                return $item->tanggal . ($item->raw_id ?? '0');
            })->values();

        // Hitung saldo dengan mempertimbangkan saldo sebelumnya
        $saldoBerjalan = $saldoSebelumnya;
        foreach ($merged as $item) {
            $saldoBerjalan += $item->uang_masuk - $item->uang_keluar;
            $item->saldo = $saldoBerjalan;
        }

        $this->reports = $merged->toArray();
    }

    private function getSaldoSampaiTanggal($tanggal)
    {
        // Hitung total dari Reportharian
        $totalReportharian = Reportharian::where('user_id', Auth::id())
            ->where('tanggal', '<=', $tanggal)
            ->sum(\DB::raw('uang_masuk - uang_keluar'));

        // Hitung total dari Capitalearly
        $totalModalAwal = Capitalearly::where('user_id', Auth::id())
            ->where('tanggal_input', '<=', $tanggal)
            ->sum('modal_awal');

        // Hitung total modal keluar
        $totalModalKeluar = 0;
        if (\Schema::hasColumn('capitals', 'jenis')) {
            $totalModalKeluar = Capital::where('user_id', Auth::id())
                ->where('jenis', 'keluar')
                ->where('tanggal', '<=', $tanggal)
                ->sum('nominal');
        }

        return $totalReportharian + $totalModalAwal - $totalModalKeluar;
    }

    public function render()
    {
        return view('livewire.reports.reportharian-page');
    }
}