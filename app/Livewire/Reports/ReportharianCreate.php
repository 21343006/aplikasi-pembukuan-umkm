<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Reportharian;
use Livewire\Attributes\Title;

class ReportharianCreate extends Component
{
    #[Title('Input Laporan Harian')]

    public $tanggal;
    public $keterangan;
    public $uang_masuk = 0;
    public $uang_keluar = 0;

    public function save()
    {
        $this->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string',
            'uang_masuk' => 'numeric|min:0',
            'uang_keluar' => 'numeric|min:0',
        ]);

        $tanggalInput = $this->tanggal;
        $uangMasuk = floatval($this->uang_masuk ?? 0);
        $uangKeluar = floatval($this->uang_keluar ?? 0);

        // Cari saldo terakhir sebelum tanggal input
        $saldoSebelumnya = Reportharian::where('tanggal', '<', $tanggalInput)
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->value('saldo') ?? 0;

        // Hitung saldo untuk data hari ini
        $saldo = $saldoSebelumnya + $uangMasuk - $uangKeluar;

        // Simpan laporan baru
        $laporanBaru = Reportharian::create([
            'tanggal' => $tanggalInput,
            'keterangan' => $this->keterangan,
            'uang_masuk' => $uangMasuk,
            'uang_keluar' => $uangKeluar,
            'saldo' => $saldo,
        ]);
        if (\Carbon\Carbon::parse($tanggalInput)->day === 1) {
            Reportharian::updateOrCreate(
                ['tanggal' => $tanggalInput, 'keterangan' => 'Saldo Awal Bulan'],
                [
                    'uang_masuk' => 0,
                    'uang_keluar' => 0,
                    'saldo' => $saldo,
                ]
            );
        }

        // Hitung ulang semua saldo setelah tanggal input
        $laporanSetelahnya = Reportharian::where('tanggal', '>', $tanggalInput)
            ->orWhere(function ($query) use ($tanggalInput, $laporanBaru) {
                $query->where('tanggal', '=', $tanggalInput)
                    ->where('id', '>', $laporanBaru->id);
            })
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        $saldoBerjalan = $saldo;

        foreach ($laporanSetelahnya as $laporan) {
            $saldoBerjalan += $laporan->uang_masuk - $laporan->uang_keluar;
            $laporan->update(['saldo' => $saldoBerjalan]);
        }

        session()->flash('success', 'Laporan berhasil disimpan.');

        $this->reset(['tanggal', 'keterangan', 'uang_masuk', 'uang_keluar']);
        return redirect()->to('/laporan-harian/list')->with('success', 'Data berhasil ditambahkan');
    }


    public function render()
    {
        return view('livewire.reports.reportharian-create');
    }
}
