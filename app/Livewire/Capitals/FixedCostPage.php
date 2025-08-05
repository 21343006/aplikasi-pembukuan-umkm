<?php

namespace App\Livewire\Capitals;

use App\Models\FixedCost;
use Livewire\Component;
use Livewire\Attributes\Title;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class FixedCostPage extends Component
{
    #[Title('Modal Tetap')]
    
    public $keperluan, $nominal;
    public $fixedCosts = [];
    public $jumlahNominal = 0;
    public $showModal = false;
    public $isEdit = false;
    public $fixed_cost_id;
    public $filterMonth = '';
    public $filterYear = '';
    
    // Array nama bulan dalam bahasa Indonesia
    public array $monthNames = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    // Rules untuk validation
    protected $rules = [
        'keperluan' => 'required|string|max:255',
        'nominal' => 'required|numeric|min:0',
    ];

    public function mount()
    {
        // Set default filter ke bulan dan tahun saat ini
        $this->filterMonth = now()->month;
        $this->filterYear = now()->year;
        $this->loadFixedCosts();
    }

    public function getMonthNameProperty()
    {
        return isset($this->monthNames[(int) $this->filterMonth])
            ? $this->monthNames[(int) $this->filterMonth]
            : '';
    }

    public function loadFixedCosts()
    {
        try {
            if ($this->filterMonth && $this->filterYear) {
                // Load data berdasarkan filter bulan dan tahun
                $this->fixedCosts = FixedCost::byMonth($this->filterMonth, $this->filterYear)
                                            ->orderBy('keperluan', 'asc')
                                            ->get();
            } else {
                // Load data bulan ini jika tidak ada filter
                $this->fixedCosts = FixedCost::byMonth(now()->month, now()->year)
                                            ->orderBy('keperluan', 'asc')
                                            ->get();
            }

            // Hitung total nominal
            $this->jumlahNominal = $this->fixedCosts->sum('nominal');

        } catch (\Exception $e) {
            $this->fixedCosts = collect([]);
            $this->jumlahNominal = 0;
            session()->flash('error', 'Terjadi kesalahan saat memuat data: ' . $e->getMessage());
        }
    }

    public function openModal()
    {
        $this->resetInput();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->resetInput();
        $this->showModal = false;
    }

    public function save()
    {
        $this->validate();

        try {
            $data = [
                'keperluan' => trim($this->keperluan),
                'nominal' => (float) $this->nominal,
            ];

            if ($this->isEdit && $this->fixed_cost_id) {
                // Update data existing
                $fixedCost = FixedCost::findOrFail($this->fixed_cost_id);
                
                // Update data untuk bulan yang sedang diedit
                $fixedCost->update($data);
                
                // Update juga data untuk bulan-bulan berikutnya yang belum lewat
                $this->updateFutureMonths($data, $fixedCost->keperluan);
                
                session()->flash('message', 'Data berhasil diperbarui untuk bulan ini dan bulan-bulan berikutnya!');
            } else {
                // Create data baru untuk bulan ini dan bulan-bulan berikutnya dalam tahun yang sama
                $this->createForCurrentAndFutureMonths($data);
                
                session()->flash('message', 'Data berhasil ditambahkan untuk bulan ini dan bulan-bulan berikutnya!');
            }

            $this->closeModal();
            $this->loadFixedCosts();
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function createForCurrentAndFutureMonths($data)
    {
        $currentMonth = $this->filterMonth ?: now()->month;
        $currentYear = $this->filterYear ?: now()->year;
        
        // Buat data untuk bulan ini sampai Desember
        for ($month = $currentMonth; $month <= 12; $month++) {
            // Cek apakah data dengan keperluan yang sama sudah ada di bulan tersebut
            $existing = FixedCost::byMonth($month, $currentYear)
                                ->where('keperluan', $data['keperluan'])
                                ->first();
            
            if (!$existing) {
                FixedCost::create([
                    'tanggal' => Carbon::create($currentYear, $month, 1)->format('Y-m-d'),
                    'keperluan' => $data['keperluan'],
                    'nominal' => $data['nominal'],
                ]);
            }
        }
    }

    private function updateFutureMonths($data, $oldKeperluan)
    {
        $currentMonth = $this->filterMonth ?: now()->month;
        $currentYear = $this->filterYear ?: now()->year;
        
        // Update data untuk bulan ini sampai Desember
        for ($month = $currentMonth; $month <= 12; $month++) {
            FixedCost::byMonth($month, $currentYear)
                    ->where('keperluan', $oldKeperluan)
                    ->update([
                        'keperluan' => $data['keperluan'],
                        'nominal' => $data['nominal'],
                    ]);
        }
    }

    public function edit($id)
    {
        try {
            $fixedCost = FixedCost::findOrFail($id);

            $this->fixed_cost_id = $fixedCost->id;
            $this->keperluan = $fixedCost->keperluan;
            $this->nominal = $fixedCost->nominal;
            $this->isEdit = true;
            $this->showModal = true;
        } catch (\Exception $e) {
            session()->flash('error', 'Data tidak ditemukan atau terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', ['id' => $id]);
    }

    public function delete($id)
    {
        try {
            $fixedCost = FixedCost::findOrFail($id);
            $keperluan = $fixedCost->keperluan;
            
            // Hapus data untuk bulan ini dan bulan-bulan berikutnya
            $currentMonth = $this->filterMonth ?: now()->month;
            $currentYear = $this->filterYear ?: now()->year;
            
            FixedCost::where('keperluan', $keperluan)
                    ->where(function($query) use ($currentMonth, $currentYear) {
                        $query->where(function($q) use ($currentYear) {
                            $q->whereYear('tanggal', $currentYear);
                        })
                        ->where(function($q) use ($currentMonth) {
                            $q->whereMonth('tanggal', '>=', $currentMonth);
                        });
                    })
                    ->delete();
                    
            $this->loadFixedCosts();
            session()->flash('message', 'Data berhasil dihapus untuk bulan ini dan bulan-bulan berikutnya!');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    public function clearFilters()
    {
        $this->filterMonth = now()->month;
        $this->filterYear = now()->year;
        $this->resetInput();
        $this->loadFixedCosts();
    }

    public function resetInput()
    {
        $this->reset([
            'keperluan',
            'nominal',
            'fixed_cost_id',
            'isEdit'
        ]);

        // Clear validation errors
        $this->resetValidation();
    }

    // Livewire lifecycle methods untuk reactive filtering
    public function updatedFilterMonth()
    {
        $this->resetInput();
        $this->loadFixedCosts();
    }

    public function updatedFilterYear()
    {
        $this->resetInput();
        $this->loadFixedCosts();
    }

    // Real-time validation
    public function updatedKeperluan()
    {
        $this->validateOnly('keperluan');
        
        // Cek apakah keperluan sudah ada di bulan yang sedang dilihat
        if ($this->keperluan && !$this->isEdit) {
            $currentMonth = $this->filterMonth ?: now()->month;
            $currentYear = $this->filterYear ?: now()->year;
            
            $existing = FixedCost::byMonth($currentMonth, $currentYear)
                                ->where('keperluan', trim($this->keperluan))
                                ->first();
            
            if ($existing) {
                $this->addError('keperluan', 'Keperluan ini sudah ada untuk bulan ' . $this->monthName . ' ' . $currentYear);
            }
        }
    }

    public function updatedNominal()
    {
        $this->validateOnly('nominal');
    }

    // Method untuk copy data dari bulan sebelumnya
    public function copyFromPreviousMonth()
    {
        try {
            $currentMonth = $this->filterMonth ?: now()->month;
            $currentYear = $this->filterYear ?: now()->year;
            
            // Ambil bulan sebelumnya
            $previousMonth = $currentMonth - 1;
            $previousYear = $currentYear;
            
            if ($previousMonth < 1) {
                $previousMonth = 12;
                $previousYear--;
            }
            
            // Ambil data dari bulan sebelumnya
            $previousData = FixedCost::byMonth($previousMonth, $previousYear)->get();
            
            if ($previousData->isEmpty()) {
                session()->flash('info', 'Tidak ada data modal tetap pada bulan sebelumnya.');
                return;
            }
            
            $copiedCount = 0;
            foreach ($previousData as $data) {
                // Cek apakah sudah ada data dengan keperluan yang sama
                $existing = FixedCost::byMonth($currentMonth, $currentYear)
                                    ->where('keperluan', $data->keperluan)
                                    ->first();
                
                if (!$existing) {
                    FixedCost::create([
                        'tanggal' => Carbon::create($currentYear, $currentMonth, 1)->format('Y-m-d'),
                        'keperluan' => $data->keperluan,
                        'nominal' => $data->nominal,
                    ]);
                    $copiedCount++;
                }
            }
            
            if ($copiedCount > 0) {
                $this->loadFixedCosts();
                session()->flash('message', "Berhasil menyalin {$copiedCount} data dari bulan sebelumnya.");
            } else {
                session()->flash('info', 'Semua data sudah ada di bulan ini.');
            }
            
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menyalin data: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.capitals.fixed-cost-page');
    }
}