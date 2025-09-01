<?php

namespace App\Livewire\Expenditures;

use App\Models\Expenditure;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ExpenditurePage extends Component
{
    use WithPagination;
    #[Title('Biaya Pengeluaran')]
    
    public $tanggal, $keterangan, $jumlah;
    public array $expenditures = [];
    public $total = 0;
    public $showModal = false;
    public $isEdit = false;
    public $expenditure_id;
    public array $monthlyTotals = [];
    public $filterMonth = '';
    public $filterYear = '';
    public $perPage = 10;
    protected $paginationTheme = 'bootstrap';

    // Array nama bulan dalam bahasa Indonesia
    public array $monthNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    protected $rules = [
        'tanggal' => 'required|date',
        'keterangan' => 'required|string|max:255',
        'jumlah' => 'required|numeric|min:0',
    ];

    protected $messages = [
        'tanggal.required' => 'Tanggal wajib diisi.',
        'tanggal.date' => 'Format tanggal tidak valid.',
        'keterangan.required' => 'Keterangan wajib diisi.',
        'keterangan.max' => 'Keterangan maksimal 255 karakter.',
        'jumlah.required' => 'Jumlah wajib diisi.',
        'jumlah.numeric' => 'Jumlah harus berupa angka.',
        'jumlah.min' => 'Jumlah tidak boleh kurang dari 0.',
    ];

    public function getMonthNameProperty()
    {
        return isset($this->monthNames[(int) $this->filterMonth])
            ? $this->monthNames[(int) $this->filterMonth]
            : '';
    }

    public function getMaxDateProperty()
    {
        if (!$this->filterMonth || !$this->filterYear) {
            return '';
        }

        try {
            $daysInMonth = Carbon::create($this->filterYear, $this->filterMonth)->daysInMonth;
            return sprintf('%04d-%02d-%02d', $this->filterYear, $this->filterMonth, $daysInMonth);
        } catch (\Exception $e) {
            Log::error('Error calculating max date: ' . $e->getMessage());
            return '';
        }
    }

    public function getMinDateProperty()
    {
        if (!$this->filterMonth || !$this->filterYear) {
            return '';
        }
        
        return sprintf('%04d-%02d-01', $this->filterYear, $this->filterMonth);
    }

    public function mount()
    {
        $this->expenditures = [];
        $this->total = 0;
        $this->monthlyTotals = [];
        $this->filterMonth = '';
        $this->filterYear = '';
        $this->loadMonthlyTotals();
    }

    // Modifikasi method loadExpenditures untuk menghitung total dari semua data (bukan hanya yang dipaginate)
    public function loadExpenditures()
    {
        try {
            if (!Auth::check()) {
                $this->expenditures = [];
                $this->total = 0;
                return;
            }

            if (!$this->filterMonth || !$this->filterYear) {
                $this->expenditures = [];
                $this->total = 0;
                return;
            }

            if (!is_numeric($this->filterMonth) || !is_numeric($this->filterYear)) {
                $this->expenditures = [];
                $this->total = 0;
                return;
            }

            // Hitung total dari semua data (untuk summary)
            $totalQuery = Expenditure::where('user_id', Auth::id())
                ->whereMonth('tanggal', (int)$this->filterMonth)
                ->whereYear('tanggal', (int)$this->filterYear);

            $this->total = $totalQuery->sum('jumlah') ?? 0;
            
        } catch (\Exception $e) {
            $this->expenditures = [];
            $this->total = 0;

            Log::error('Error in loadExpenditures: ' . $e->getMessage(), [
                'filterMonth' => $this->filterMonth,
                'filterYear' => $this->filterYear,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            session()->flash('error', 'Terjadi kesalahan saat memuat data. Silakan coba lagi.');
        }
    }

    // Tambahkan method baru untuk mendapatkan data yang dipaginate
    public function getPaginatedExpenditures()
    {
        if (!Auth::check() || !$this->filterMonth || !$this->filterYear) {
            // Return query builder dengan where yang tidak akan pernah match
            return Expenditure::where('id', 0)->paginate($this->perPage);
        }

        if (!is_numeric($this->filterMonth) || !is_numeric($this->filterYear)) {
            return Expenditure::where('id', 0)->paginate($this->perPage);
        }

        try {
            return Expenditure::where('user_id', Auth::id())
                ->whereMonth('tanggal', (int)$this->filterMonth)
                ->whereYear('tanggal', (int)$this->filterYear)
                ->orderBy('tanggal', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate($this->perPage);
        } catch (\Exception $e) {
            Log::error('Error in getPaginatedExpenditures: ' . $e->getMessage());
            return Expenditure::where('id', 0)->paginate($this->perPage);
        }
    }

    public function loadMonthlyTotals()
    {
        try {
            if (!Auth::check()) {
                $this->monthlyTotals = [];
                return;
            }

            // Buat query dasar untuk user yang sedang login
            $query = Expenditure::where('user_id', Auth::id())
                ->whereNotNull('tanggal');

            // Jika tahun dipilih, filter berdasarkan tahun
            if (!empty($this->filterYear) && is_numeric($this->filterYear)) {
                $query->whereYear('tanggal', (int)$this->filterYear);
            }

            try {
                // Coba gunakan selectRaw untuk performa yang lebih baik
                $monthlyData = $query->selectRaw('
                    YEAR(tanggal) as year, 
                    MONTH(tanggal) as month, 
                    SUM(jumlah) as total
                ')
                    ->groupByRaw('YEAR(tanggal), MONTH(tanggal)')
                    ->orderByRaw('YEAR(tanggal) DESC, MONTH(tanggal) DESC')
                    ->get();

                $this->monthlyTotals = $monthlyData->mapWithKeys(function ($item) {
                    $key = sprintf('%04d-%02d', $item->year, $item->month);
                    return [$key => $item->total];
                })->toArray();

            } catch (\Exception $rawQueryException) {
                // Fallback ke method manual jika selectRaw gagal
                Log::warning('selectRaw failed, using fallback method: ' . $rawQueryException->getMessage());
                
                $expenditures = $query->get();

                if ($expenditures->isEmpty()) {
                    $this->monthlyTotals = [];
                    return;
                }

                $grouped = $expenditures->groupBy(function ($expenditure) {
                    try {
                        $date = $expenditure->tanggal instanceof \Carbon\Carbon
                            ? $expenditure->tanggal
                            : Carbon::parse($expenditure->tanggal);

                        return $date->format('Y-m');
                    } catch (\Exception $dateException) {
                        Log::error('Error parsing date in groupBy: ' . $dateException->getMessage());
                        return null;
                    }
                });

                $this->monthlyTotals = $grouped->filter()
                    ->map(function ($monthlyExpenditures) {
                        return $monthlyExpenditures->sum('jumlah');
                    })
                    ->sortKeysDesc()
                    ->toArray();
            }

        } catch (\Exception $e) {
            $this->monthlyTotals = [];
            Log::error('Error loading monthly totals: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'filterYear' => $this->filterYear,
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

        if (!$this->filterMonth || !$this->filterYear) {
            session()->flash('error', 'Silakan pilih bulan dan tahun terlebih dahulu.');
            return;
        }

        $this->resetInput();

        try {
            $today = now();
            if ($today->month == $this->filterMonth && $today->year == $this->filterYear) {
                $this->tanggal = $today->format('Y-m-d');
            } else {
                $this->tanggal = sprintf('%04d-%02d-01', $this->filterYear, $this->filterMonth);
            }
        } catch (\Exception $e) {
            $this->tanggal = now()->format('Y-m-d');
            Log::error('Error setting default date: ' . $e->getMessage());
        }

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

            $selectedDate = Carbon::parse($this->tanggal);
            if ($selectedDate->month != $this->filterMonth || $selectedDate->year != $this->filterYear) {
                $this->addError('tanggal', 'Tanggal harus sesuai dengan bulan dan tahun yang dipilih.');
                return;
            }

            $data = [
                'user_id' => Auth::id(),
                'tanggal' => $this->tanggal,
                'keterangan' => trim($this->keterangan),
                'jumlah' => (float) $this->jumlah,
            ];

            if ($this->isEdit && $this->expenditure_id) {
                $expenditure = Expenditure::where('user_id', Auth::id())->findOrFail($this->expenditure_id);
                $expenditure->update($data);
                session()->flash('message', 'Data berhasil diperbarui!');
            } else {
                Expenditure::create($data);
                session()->flash('message', 'Data berhasil ditambahkan!');
            }

            $this->closeModal();
            $this->loadExpenditures();
            $this->loadMonthlyTotals();
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Expenditure not found: ' . $e->getMessage(), [
                'expenditure_id' => $this->expenditure_id,
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

            $expenditure = Expenditure::where('user_id', Auth::id())->findOrFail($id);

            $this->expenditure_id = $expenditure->id;

            if ($expenditure->tanggal instanceof \Carbon\Carbon) {
                $this->tanggal = $expenditure->tanggal->format('Y-m-d');
            } else {
                $this->tanggal = Carbon::parse((string) $expenditure->tanggal)->format('Y-m-d');
            }

            $this->keterangan = $expenditure->keterangan;
            $this->jumlah = $expenditure->jumlah;
            $this->isEdit = true;
            $this->showModal = true;
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Expenditure not found for edit: ' . $e->getMessage(), [
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

            $expenditure = Expenditure::where('user_id', Auth::id())->findOrFail($id);
            $expenditure->delete();
            
            $this->loadExpenditures();
            $this->loadMonthlyTotals();
            session()->flash('message', 'Data berhasil dihapus!');
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Expenditure not found for delete: ' . $e->getMessage(), [
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

    public function exportCSV()
    {
        try {
            if (!Auth::check()) {
                session()->flash('error', 'Anda harus login terlebih dahulu.');
                return;
            }

            if (!$this->filterMonth || !$this->filterYear) {
                session()->flash('error', 'Silakan pilih bulan dan tahun terlebih dahulu untuk export.');
                return;
            }

            $expenditures = Expenditure::where('user_id', Auth::id())
                ->whereMonth('tanggal', (int)$this->filterMonth)
                ->whereYear('tanggal', (int)$this->filterYear)
                ->orderBy('tanggal', 'desc')
                ->get();

            if ($expenditures->isEmpty()) {
                session()->flash('error', 'Tidak ada data untuk di-export.');
                return;
            }

            $monthName = $this->monthNames[(int)$this->filterMonth];
            $fileName = sprintf('pengeluaran_%s_%s.csv', $monthName, $this->filterYear);

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ];

            $callback = function() use ($expenditures) {
                $file = fopen('php://output', 'w');
                
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

                fputcsv($file, [
                    'No',
                    'Tanggal',
                    'Keterangan',
                    'Jumlah',
                    'Tanggal Dibuat',
                    'Tanggal Diupdate'
                ]);

                $no = 1;
                foreach ($expenditures as $expenditure) {
                    fputcsv($file, [
                        $no++,
                        Carbon::parse($expenditure->tanggal)->format('Y-m-d'),
                        $expenditure->keterangan,
                        $expenditure->jumlah,
                        Carbon::parse($expenditure->created_at)->format('Y-m-d H:i:s'),
                        Carbon::parse($expenditure->updated_at)->format('Y-m-d H:i:s')
                    ]);
                }

                fputcsv($file, []); // Empty row
                fputcsv($file, [
                    '', '', 'TOTAL:',
                    $this->total,
                    '', ''
                ]);

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            Log::error('Error in exportCSV method: ' . $e->getMessage(), [
                'filterMonth' => $this->filterMonth,
                'filterYear' => $this->filterYear,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            session()->flash('error', 'Terjadi kesalahan saat export data. Silakan coba lagi.');
        }
    }

    public function clearFilters()
    {
        $this->filterMonth = '';
        $this->filterYear = '';
        $this->expenditures = [];
        $this->total = 0;
        $this->resetInput();
        $this->resetPage(); // Reset pagination saat clear filter
        $this->loadMonthlyTotals(); // Reload monthly totals after clearing filters
    }

    public function resetInput()
    {
        $this->reset([
            'tanggal',
            'keterangan',
            'jumlah',
            'expenditure_id',
            'isEdit'
        ]);

        $this->resetValidation();
    }

    public function updatedFilterMonth()
    {
        $this->resetInput();
        $this->resetPage(); // Reset pagination saat ganti filter
        $this->loadExpenditures();
    }

    public function updatedFilterYear()
    {
        $this->resetInput();
        $this->resetPage(); // Reset pagination saat ganti filter
        $this->loadExpenditures();
        $this->loadMonthlyTotals(); // Reload monthly totals when year filter changes
    }

    // Tambahkan method untuk mengubah jumlah data per halaman
    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function updatedJumlah()
    {
        $this->validateOnly('jumlah');
    }

    public function updatedKeterangan()
    {
        $this->validateOnly('keterangan');
    }

    public function updatedTanggal()
    {
        $this->validateOnly('tanggal');
    }

    public function getExpendituresCollection()
    {
        return collect($this->expenditures);
    }

    public function getAvailableYears()
    {
        $currentYear = now()->year;
        $startYear = 2020;
        
        return range($currentYear, $startYear);
    }

    public function render()
    {
        // Dapatkan data yang dipaginate untuk ditampilkan
        $paginatedExpenditures = $this->getPaginatedExpenditures();
        
        return view('livewire.expenditures.expenditure-page', [
            'paginatedExpenditures' => $paginatedExpenditures
        ]);
    }
}