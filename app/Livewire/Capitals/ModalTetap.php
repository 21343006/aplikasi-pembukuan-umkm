<?php

namespace App\Livewire\Capitals;

use App\Models\Capital;
use Livewire\Attributes\Title;
use Livewire\Component;

class ModalTetap extends Component
{
    #[Title('Modal Tetap')]
    public function render()
    {
        return view('livewire.capitals.modal-tetap', [
            'capitals' => Capital::all(),
            'jumlah' => Capital::sum('nominal') // ✅ total seluruh nominal
        ]);
    }
}