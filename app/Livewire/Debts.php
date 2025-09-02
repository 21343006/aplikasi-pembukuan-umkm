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
        $debts = Debt::where('user_id', auth()->id())
            ->orderBy('due_date', 'asc')
            ->paginate(10);

        $totalDebts = Debt::where('user_id', auth()->id())->sum('amount');
        $totalPaid = Debt::where('user_id', auth()->id())->sum('paid_amount');
        $totalRemaining = $totalDebts - $totalPaid;
        $overdueCount = Debt::where('user_id', auth()->id())
            ->where('due_date', '<', now())
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
        $debt = Debt::findOrFail($id);
        $this->editingDebtId = $id;
        $this->creditor_name = $debt->creditor_name;
        $this->description = $debt->description;
        $this->amount = $debt->amount;
        $this->due_date = $debt->due_date->format('Y-m-d');
        $this->notes = $debt->notes;
        $this->showForm = true;
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
        $debt = Debt::findOrFail($id);
        $debt->delete();
        session()->flash('message', 'Utang berhasil dihapus!');
    }

    public function showPayment($id)
    {
        $this->selectedDebt = Debt::findOrFail($id);
        $this->payment_amount = '';
        $this->payment_date = now()->format('Y-m-d');
        $this->showPaymentForm = true;
    }

    public function recordPayment()
    {
        $this->validate([
            'payment_amount' => 'required|numeric|min:0|max:' . $this->selectedDebt->remaining_amount,
            'payment_date' => 'required|date',
        ]);

        $debt = $this->selectedDebt;
        $newPaidAmount = $debt->paid_amount + $this->payment_amount;
        
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
