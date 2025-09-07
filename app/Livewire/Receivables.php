<?php

namespace App\Livewire;

use App\Models\Receivable;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;

class Receivables extends Component
{
    use WithPagination;

    #[Rule('required|string|max:255')]
    public $debtor_name = '';

    #[Rule('required|string')]
    public $description = '';

    #[Rule('required|numeric|min:0')]
    public $amount = '';

    #[Rule('required|date|after_or_equal:today')]
    public $due_date = '';

    #[Rule('nullable|string')]
    public $notes = '';

    public $editingReceivableId = null;
    public $showForm = false;
    public $showPaymentForm = false;
    public $selectedReceivable = null;
    public $payment_amount = '';
    public $payment_date = '';

    protected $listeners = ['refresh' => '$refresh'];

    public function render()
    {
        $receivables = Receivable::
            orderBy('due_date', 'asc')
            ->paginate(10);

        $totalReceivables = Receivable::sum('amount');
        $totalPaid = Receivable::where('paid_amount', '>', 0)->sum('paid_amount');
        $totalRemaining = $totalReceivables - $totalPaid;
        $overdueCount = Receivable::
            where('due_date', '<', now())
            ->where('status', '!=', 'paid')
            ->count();

        return view('livewire.receivables', compact('receivables', 'totalReceivables', 'totalPaid', 'totalRemaining', 'overdueCount'));
    }

    public function create()
    {
        $this->resetForm();
        $this->showForm = true;
        $this->editingReceivableId = null;
    }

    public function edit($id)
    {
        try {
            // Pastikan user yang login
            if (!auth()->check()) {
                session()->flash('error', 'Anda harus login terlebih dahulu.');
                return;
            }

            // Gunakan global scope untuk memastikan hanya data user yang sedang login
            $receivable = Receivable::findOrFail($id);
            
            // Double check: pastikan receivable milik user yang sedang login
            if ($receivable->user_id !== auth()->id()) {
                session()->flash('error', 'Anda tidak memiliki akses ke data ini.');
                return;
            }

            $this->editingReceivableId = $id;
            $this->debtor_name = $receivable->debtor_name;
            $this->description = $receivable->description;
            $this->amount = $receivable->amount;
            $this->due_date = $receivable->due_date->format('Y-m-d');
            $this->notes = $receivable->notes;
            $this->showForm = true;
        } catch (\Exception $e) {
            session()->flash('error', 'Data tidak ditemukan atau Anda tidak memiliki akses.');
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'user_id' => auth()->id(),
            'debtor_name' => $this->debtor_name,
            'description' => $this->description,
            'amount' => $this->amount,
            'due_date' => $this->due_date,
            'notes' => $this->notes,
        ];

        if ($this->editingReceivableId) {
            Receivable::findOrFail($this->editingReceivableId)->update($data);
            session()->flash('message', 'Piutang berhasil diperbarui!');
        } else {
            Receivable::create($data);
            session()->flash('message', 'Piutang berhasil ditambahkan!');
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function delete($id)
    {
        try {
            // Pastikan user yang login
            if (!auth()->check()) {
                session()->flash('error', 'Anda harus login terlebih dahulu.');
                return;
            }

            // Gunakan global scope untuk memastikan hanya data user yang sedang login
            $receivable = Receivable::findOrFail($id);
            
            // Double check: pastikan receivable milik user yang sedang login
            if ($receivable->user_id !== auth()->id()) {
                session()->flash('error', 'Anda tidak memiliki akses ke data ini.');
                return;
            }

            $receivable->delete();
            session()->flash('message', 'Piutang berhasil dihapus!');
        } catch (\Exception $e) {
            session()->flash('error', 'Data tidak ditemukan atau Anda tidak memiliki akses.');
        }
    }

    public function showPayment($id)
    {
        try {
            // Pastikan user yang login
            if (!auth()->check()) {
                session()->flash('error', 'Anda harus login terlebih dahulu.');
                return;
            }

            // Gunakan global scope untuk memastikan hanya data user yang sedang login
            $this->selectedReceivable = Receivable::findOrFail($id);
            
            // Double check: pastikan receivable milik user yang sedang login
            if ($this->selectedReceivable->user_id !== auth()->id()) {
                session()->flash('error', 'Anda tidak memiliki akses ke data ini.');
                return;
            }

            $this->payment_amount = '';
            $this->payment_date = now()->format('Y-m-d');
            $this->showPaymentForm = true;
        } catch (\Exception $e) {
            session()->flash('error', 'Data tidak ditemukan atau Anda tidak memiliki akses.');
        }
    }

    public function recordPayment()
    {
        $this->validate([
            'payment_amount' => 'required|numeric|min:0|max:' . $this->selectedReceivable->remaining_amount,
            'payment_date' => 'required|date',
        ]);

        $receivable = $this->selectedReceivable;
        $currentPaidAmount = $receivable->paid_amount ?? 0;
        $newPaidAmount = $currentPaidAmount + $this->payment_amount;
        
        // Update status berdasarkan jumlah pembayaran
        if ($newPaidAmount >= $receivable->amount) {
            $status = 'paid';
        } elseif ($newPaidAmount > 0) {
            $status = 'partial';
        } else {
            $status = 'unpaid';
        }

        $receivable->update([
            'paid_amount' => $newPaidAmount,
            'paid_date' => $this->payment_date,
            'status' => $status,
        ]);

        session()->flash('message', 'Pembayaran berhasil dicatat!');
        $this->showPaymentForm = false;
        $this->selectedReceivable = null;
    }

    public function resetForm()
    {
        $this->debtor_name = '';
        $this->description = '';
        $this->amount = '';
        $this->due_date = '';
        $this->notes = '';
        $this->editingReceivableId = null;
    }

    public function cancel()
    {
        $this->resetForm();
        $this->showForm = false;
        $this->showPaymentForm = false;
        $this->selectedReceivable = null;
    }
}
