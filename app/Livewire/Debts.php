<?php

namespace App\Livewire;

use App\Models\Debt;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;

class Debts extends Component
{
    use WithPagination;

    #[Rule('required|string|max:255')]
    public $creditor_name = '';

    #[Rule('required|string')]
    public $description = '';

    #[Rule('required|numeric|min:0')]
    public $amount = '';

    #[Rule('required|date|after_or_equal:today')]
    public $due_date = '';

    #[Rule('nullable|string')]
    public $notes = '';

    public $editingDebtId = null;
    public $showForm = false;
    public $showPaymentForm = false;
    public $selectedDebt = null;
    public $payment_amount = '';
    public $payment_date = '';

    protected $listeners = ['refresh' => '$refresh'];

    public function render()
    {
        $debts = Debt::
            orderBy('due_date', 'asc')
            ->paginate(10);

        $totalDebts = Debt::sum('amount');
        $totalPaid = Debt::where('paid_amount', '>', 0)->sum('paid_amount');
        $totalRemaining = $totalDebts - $totalPaid;
        $overdueCount = Debt::
            where('due_date', '<', now())
            ->where('status', '!=', 'paid')
            ->count();

        return view('livewire.debts', compact('debts', 'totalDebts', 'totalPaid', 'totalRemaining', 'overdueCount'));
    }

    public function create()
    {
        $this->resetForm();
        $this->showForm = true;
        $this->editingDebtId = null;
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
            $debt = Debt::findOrFail($id);
            
            // Double check: pastikan debt milik user yang sedang login
            if ($debt->user_id !== auth()->id()) {
                session()->flash('error', 'Anda tidak memiliki akses ke data ini.');
                return;
            }

            $this->editingDebtId = $id;
            $this->creditor_name = $debt->creditor_name;
            $this->description = $debt->description;
            $this->amount = $debt->amount;
            $this->due_date = $debt->due_date->format('Y-m-d');
            $this->notes = $debt->notes;
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
            'creditor_name' => $this->creditor_name,
            'description' => $this->description,
            'amount' => $this->amount,
            'due_date' => $this->due_date,
            'notes' => $this->notes,
        ];

        if ($this->editingDebtId) {
            Debt::findOrFail($this->editingDebtId)->update($data);
            session()->flash('message', 'Utang berhasil diperbarui!');
        } else {
            Debt::create($data);
            session()->flash('message', 'Utang berhasil ditambahkan!');
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
            $debt = Debt::findOrFail($id);
            
            // Double check: pastikan debt milik user yang sedang login
            if ($debt->user_id !== auth()->id()) {
                session()->flash('error', 'Anda tidak memiliki akses ke data ini.');
                return;
            }

            $debt->delete();
            session()->flash('message', 'Utang berhasil dihapus!');
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
            $this->selectedDebt = Debt::findOrFail($id);
            
            // Double check: pastikan debt milik user yang sedang login
            if ($this->selectedDebt->user_id !== auth()->id()) {
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
            'payment_amount' => 'required|numeric|min:0|max:' . $this->selectedDebt->remaining_amount,
            'payment_date' => 'required|date',
        ]);

        $debt = $this->selectedDebt;
        $currentPaidAmount = $debt->paid_amount ?? 0;
        $newPaidAmount = $currentPaidAmount + $this->payment_amount;
        
        // Update status berdasarkan jumlah pembayaran
        if ($newPaidAmount >= $debt->amount) {
            $status = 'paid';
        } elseif ($newPaidAmount > 0) {
            $status = 'partial';
        } else {
            $status = 'unpaid';
        }

        $debt->update([
            'paid_amount' => $newPaidAmount,
            'paid_date' => $this->payment_date,
            'status' => $status,
        ]);

        session()->flash('message', 'Pembayaran berhasil dicatat!');
        $this->showPaymentForm = false;
        $this->selectedDebt = null;
    }

    public function resetForm()
    {
        $this->creditor_name = '';
        $this->description = '';
        $this->amount = '';
        $this->due_date = '';
        $this->notes = '';
        $this->editingDebtId = null;
    }

    public function cancel()
    {
        $this->resetForm();
        $this->showForm = false;
        $this->showPaymentForm = false;
        $this->selectedDebt = null;
    }
}
