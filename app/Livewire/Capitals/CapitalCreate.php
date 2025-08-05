<?php

namespace App\Livewire\Capitals;

use App\Models\Capital;
use Livewire\Attributes\Title;
use Livewire\Component;

class CapitalCreate extends Component
{
    #[Title('Tambah Modal Awal')]

    public $nama;
    public $tanggal;
    public $keperluan;
    public $keterangan;
    public $nominal;

    public function render()
    {
        $jumlah = Capital::sum('nominal'); // ini akumulasi semua nominal
        return view('livewire.capitals.capital-create', [
            'jumlah' => $jumlah,
        ]);
    }

    public function create()
    {
        // Validasi sebelum simpan
        $validated = $this->validate([
            'nama'       => 'required|string|max:255',
            'tanggal'    => 'required|date',
            'keperluan'  => 'nullable|string|max:255',
            'keterangan' => 'nullable|string|max:255',
            'nominal'    => 'required|numeric|min:0',
        ]);

        // Simpan ke database
        Capital::create($validated);

        // Redirect ke halaman daftar modal
        return $this->redirect('/capitals', navigate: true);
    }
}
