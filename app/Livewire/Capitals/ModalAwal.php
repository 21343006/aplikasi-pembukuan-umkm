<?php

namespace App\Livewire\Capitals;

use App\Models\Capital;
use Livewire\Attributes\Title;
use Livewire\Component;

class ModalAwal extends Component
{
    #[Title('Catatan Keperluan Modal')]
    public function render()
    {
        return view('livewire.capitals.modal-awal', [
            'capitals' => Capital::all(),
            'jumlah' => Capital::sum('nominal') // ✅ total seluruh nominal
        ]);
    }
}
