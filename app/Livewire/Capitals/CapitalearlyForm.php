<?php

namespace App\Livewire\Capitals;

use Livewire\Component;
use App\Models\Capitalearly;
use Livewire\Attributes\Title;

class CapitalearlyForm extends Component
{
    #[Title('Form Modal Awal')]
    public $modal_awal;

    public function save()
    {
        $this->validate([
            'modal_awal' => 'required|numeric|min:0',
        ]);

        Capitalearly::create([
            'modal_awal' => $this->modal_awal,
        ]);

        session()->flash('success', 'Modal awal berhasil disimpan.');
        $this->reset('modal_awal');
    }

    public function render()
    {
        return view('livewire.capitals.capitalearly-form', [
            'capitals' => Capitalearly::all(),
            'total_modal' => Capitalearly::sum('modal_awal'),
        ]);
    }
}
