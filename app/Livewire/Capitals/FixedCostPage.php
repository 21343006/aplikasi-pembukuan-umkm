<?php

namespace App\Livewire\Capitals;

use App\Models\FixedCost;
use Livewire\Component;
use Livewire\Attributes\Title;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class FixedCostPage extends Component
{
    #[Title('Modal Tetap')]
    
    public $id;
    public $keperluan, $nominal;
    public array $fixedCosts = [];
    public $jumlahNominal = 0;
    public $showModal = false;
    public $isEdit = false;
    public $fixed_cost_id;
    public array $monthlyTotals = [];
    public $filterMonth = '';
    public $filterYear = '';

    // Array nama bulan dalam bahasa Indonesia
    public array $monthNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];

    protected $rules = [
        'keperluan' => 'required|string|max:255',
        'nominal' => 'required|numeric|min:0',
    ];

    protected $messages = [
        'keperluan.required' => 'Keperluan wajib diisi.',
        'keperluan.max' => 'Keperluan maksimal 255 karakter.',
        'nominal.required' => 'Nominal wajib diisi.',
        'nominal.numeric' => 'Nominal harus berupa angka.',
        'nominal.min' => 'Nominal tidak boleh kurang dari 0.',
    ];

    public function getMonthNameProperty()
    {
        return isset($this->monthNames[(int) $this->filterMonth])
            ? $this->monthNames[(int) $this->filterMonth]
            : '';
    }

    public function mount()
    {
        $this->id = uniqid();
        $this->filterMonth = now()->month;
        $this->filterYear = now()->year;
        $this->fixedCosts = [];
        $this->jumlahNominal = 0;
        $this->monthlyTotals = [];
        $this->loadFixedCosts();
        $this->loadMonthlyTotals();
    }

    public function loadFixedCosts()
    {
        try {
            if (!Auth::check()) {
                $this->fixedCosts = [];
                $this->jumlahNominal = 0;
                return;
            }

            if (!$this->filterMonth || !$this->filterYear) {
                $this->fixedCosts = [];
                $this->jumlahNominal = 0;
                return;
            }

            if (!is_numeric($this->filterMonth) || !is_numeric($this->filterYear)) {
                $this->fixedCosts = [];
                $this->jumlahNominal = 0;
                return;
            }

            $month = (int) $this->filterMonth;
            $year = (int) $this->filterYear;

            $query = FixedCost::where('user_id', Auth::id())
                ->whereMonth('tanggal', $month)
                ->whereYear('tanggal', $year)
                ->orderBy('keperluan', 'asc');

            $result = $query->get();
            $this->fixedCosts = $result->toArray();
            $this->jumlahNominal = $result->sum('nominal') ?? 0;

        } catch (\Exception $e) {
            $this->fixedCosts = [];
            $this->jumlahNominal = 0;

            Log::error('Error in loadFixedCosts: ' . $e->getMessage(), [
                'filterMonth' => $this->filterMonth,
                'filterYear' => $this->filterYear,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            session()->flash('error', 'Terjadi kesalahan saat memuat data. Silakan coba lagi.');
        }
    }

    public function loadMonthlyTotals()
    {
        try {
            if (!Auth::check()) {
                $this->monthlyTotals = [];
                return;
            }

            $monthlyData = FixedCost::selectRaw('
                YEAR(tanggal) as year, 
                MONTH(tanggal) as month, 
                SUM(nominal) as total
            ')
                ->where('user_id', Auth::id())
                ->whereNotNull('tanggal')
                ->groupByRaw('YEAR(tanggal), MONTH(tanggal)')
                ->orderByRaw('YEAR(tanggal) DESC, MONTH(tanggal) DESC')
                ->get();

            $this->monthlyTotals = $monthlyData->mapWithKeys(function ($item) {
                $key = sprintf('%04d-%02d', $item->year, $item->month);
                return [$key => $item->total];
            })->toArray();
            
        } catch (\Exception $e) {
            try {
                $fixedCosts = FixedCost::where('user_id', Auth::id())
                    ->whereNotNull('tanggal')
                    ->get();

                if ($fixedCosts->isEmpty()) {
                    $this->monthlyTotals = [];
                    return;
                }

                $grouped = $fixedCosts->groupBy(function ($fixedCost) {
                    try {
                        $date = $fixedCost->tanggal instanceof \Carbon\Carbon
                            ? $fixedCost->tanggal
                            : Carbon::parse($fixedCost->tanggal);

                        return $date->format('Y-m');
                    } catch (\Exception $dateException) {
                        Log::error('Error parsing date in groupBy: ' . $dateException->getMessage());
                        return null;
                    }
                });

                $this->monthlyTotals = $grouped->filter()
                    ->map(function ($monthlyFixedCosts) {
                        return $monthlyFixedCosts->sum('nominal');
                    })
                    ->sortKeysDesc()
                    ->toArray();
                    
            } catch (\Exception $fallbackError) {
                $this->monthlyTotals = [];
                Log::error('Error in loadMonthlyTotals fallback: ' . $fallbackError->getMessage());
            }

            Log::error('Error loading monthly totals: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function openModal()
    {
        if (!Auth::check()) {
            session()->flash('error', 'Anda harus login terlebih dahulu.');
            return;
        }

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
        try {
            if (!Auth::check()) {
                session()->flash('error', 'Anda harus login terlebih dahulu.');
                return;
            }

            $this->validate();

            $data = [
                'user_id' => Auth::id(),
                'keperluan' => trim($this->keperluan),
                'nominal' => (float) $this->nominal,
            ];

            if ($this->isEdit && $this->fixed_cost_id) {
                $fixedCost = FixedCost::where('user_id', Auth::id())->findOrFail($this->fixed_cost_id);
                $oldKeperluan = $fixedCost->keperluan;
                $fixedCost->update($data);
                $this->updateFutureMonths($data, $oldKeperluan);
                session()->flash('message', 'Data berhasil diperbarui untuk bulan ini dan bulan-bulan berikutnya!');
            } else {
                $this->createForCurrentAndFutureMonths($data);
                session()->flash('message', 'Data berhasil ditambahkan untuk bulan ini dan bulan-bulan berikutnya!');
            }

            $this->closeModal();
            $this->loadFixedCosts();
            $this->loadMonthlyTotals();

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('FixedCost not found: ' . $e->getMessage(), [
                'fixed_cost_id' => $this->fixed_cost_id,
                'user_id' => Auth::id()
            ]);
            session()->flash('error', 'Data tidak ditemukan atau Anda tidak memiliki akses.');
        } catch (\Exception $e) {
            Log::error('Error in save method: ' . $e->getMessage(), [
                'data' => $data ?? null,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.');
        }
    }

    private function createForCurrentAndFutureMonths($data)
    {
        try {
            $month = (int) ($this->filterMonth ?: now()->month);
            $year = (int) ($this->filterYear ?: now()->year);

            for ($m = $month; $m <= 12; $m++) {
                $exists = FixedCost::where('user_id', Auth::id())
                    ->whereMonth('tanggal', $m)
                    ->whereYear('tanggal', $year)
                    ->where('keperluan', $data['keperluan'])
                    ->first();

                if (!$exists) {
                    FixedCost::create([
                        'user_id' => Auth::id(),
                        'tanggal' => Carbon::create($year, $m, 1)->format('Y-m-d'),
                        'keperluan' => $data['keperluan'],
                        'nominal' => $data['nominal'],
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error in createForCurrentAndFutureMonths: ' . $e->getMessage());
            throw $e;
        }
    }

    private function updateFutureMonths($data, $oldKeperluan)
    {
        try {
            $month = (int) ($this->filterMonth ?: now()->month);
            $year = (int) ($this->filterYear ?: now()->year);

            for ($m = $month; $m <= 12; $m++) {
                FixedCost::where('user_id', Auth::id())
                    ->whereMonth('tanggal', $m)
                    ->whereYear('tanggal', $year)
                    ->where('keperluan', $oldKeperluan)
                    ->update([
                        'keperluan' => $data['keperluan'],
                        'nominal' => $data['nominal']
                    ]);
            }
        } catch (\Exception $e) {
            Log::error('Error in updateFutureMonths: ' . $e->getMessage());
            throw $e;
        }
    }

    public function edit($id)
    {
        try {
            if (!Auth::check()) {
                session()->flash('error', 'Anda harus login terlebih dahulu.');
                return;
            }

            if (!is_numeric($id) || $id <= 0) {
                session()->flash('error', 'ID tidak valid.');
                return;
            }

            $fixedCost = FixedCost::where('user_id', Auth::id())->findOrFail($id);

            $this->fixed_cost_id = $fixedCost->id;
            $this->keperluan = $fixedCost->keperluan;
            $this->nominal = $fixedCost->nominal;
            $this->isEdit = true;
            $this->showModal = true;

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('FixedCost not found for edit: ' . $e->getMessage(), [
                'id' => $id,
                'user_id' => Auth::id()
            ]);
            session()->flash('error', 'Data tidak ditemukan atau Anda tidak memiliki akses.');
        } catch (\Exception $e) {
            Log::error('Error in edit method: ' . $e->getMessage(), [
                'id' => $id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Terjadi kesalahan saat memuat data untuk diedit.');
        }
    }

    public function confirmDelete($id)
    {
        $this->delete($id);
    }

    public function delete($id)
    {
        try {
            if (!Auth::check()) {
                session()->flash('error', 'Anda harus login terlebih dahulu.');
                return;
            }

            if (!is_numeric($id) || $id <= 0) {
                session()->flash('error', 'ID tidak valid.');
                return;
            }

            $fixedCost = FixedCost::where('user_id', Auth::id())->findOrFail($id);
            $month = (int) ($this->filterMonth ?: now()->month);
            $year = (int) ($this->filterYear ?: now()->year);

            FixedCost::where('user_id', Auth::id())
                ->where('keperluan', $fixedCost->keperluan)
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', '>=', $month)
                ->delete();

            $this->loadFixedCosts();
            $this->loadMonthlyTotals();
            session()->flash('message', 'Data berhasil dihapus untuk bulan ini dan bulan-bulan berikutnya!');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('FixedCost not found for delete: ' . $e->getMessage(), [
                'id' => $id,
                'user_id' => Auth::id()
            ]);
            session()->flash('error', 'Data tidak ditemukan atau Anda tidak memiliki akses.');
        } catch (\Exception $e) {
            Log::error('Error in delete method: ' . $e->getMessage(), [
                'id' => $id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Gagal menghapus data. Silakan coba lagi.');
        }
    }

    public function clearFilters()
    {
        $this->filterMonth = now()->month;
        $this->filterYear = now()->year;
        $this->fixedCosts = [];
        $this->jumlahNominal = 0;
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

        $this->resetValidation();
    }

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

    public function updatedKeperluan()
    {
        $this->validateOnly('keperluan');

        if ($this->keperluan && !$this->isEdit) {
            try {
                $month = (int) ($this->filterMonth ?: now()->month);
                $year = (int) ($this->filterYear ?: now()->year);

                $exists = FixedCost::where('user_id', Auth::id())
                    ->whereMonth('tanggal', $month)
                    ->whereYear('tanggal', $year)
                    ->where('keperluan', trim($this->keperluan))
                    ->first();

                if ($exists) {
                    $this->addError('keperluan', 'Keperluan ini sudah ada untuk bulan ' . $this->monthName . ' ' . $year);
                }
            } catch (\Exception $e) {
                Log::error('Error checking existing keperluan: ' . $e->getMessage());
            }
        }
    }

    public function updatedNominal()
    {
        $this->validateOnly('nominal');
    }

    public function copyFromPreviousMonth()
    {
        try {
            if (!Auth::check()) {
                session()->flash('error', 'Anda harus login terlebih dahulu.');
                return;
            }

            $month = (int) ($this->filterMonth ?: now()->month);
            $year = (int) ($this->filterYear ?: now()->year);
            $prevMonth = $month - 1;
            $prevYear = $year;

            if ($prevMonth < 1) {
                $prevMonth = 12;
                $prevYear--;
            }

            $previousData = FixedCost::where('user_id', Auth::id())
                ->whereMonth('tanggal', $prevMonth)
                ->whereYear('tanggal', $prevYear)
                ->get();

            if ($previousData->isEmpty()) {
                session()->flash('info', 'Tidak ada data modal tetap pada bulan sebelumnya.');
                return;
            }

            $copiedCount = 0;
            foreach ($previousData as $data) {
                $exists = FixedCost::where('user_id', Auth::id())
                    ->whereMonth('tanggal', $month)
                    ->whereYear('tanggal', $year)
                    ->where('keperluan', $data->keperluan)
                    ->first();

                if (!$exists) {
                    FixedCost::create([
                        'user_id' => Auth::id(),
                        'tanggal' => Carbon::create($year, $month, 1)->format('Y-m-d'),
                        'keperluan' => $data->keperluan,
                        'nominal' => $data->nominal,
                    ]);
                    $copiedCount++;
                }
            }

            if ($copiedCount > 0) {
                $this->loadFixedCosts();
                $this->loadMonthlyTotals();
                session()->flash('message', "Berhasil menyalin {$copiedCount} data dari bulan sebelumnya.");
            } else {
                session()->flash('info', 'Semua data sudah ada di bulan ini.');
            }

        } catch (\Exception $e) {
            Log::error('Error in copyFromPreviousMonth: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Gagal menyalin data. Silakan coba lagi.');
        }
    }

    public function getFixedCostsCollection()
    {
        return collect($this->fixedCosts);
    }

    public function render()
    {
        return view('livewire.capitals.fixed-cost-page');
    }
}