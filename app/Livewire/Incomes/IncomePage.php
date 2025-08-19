<?php

namespace App\Livewire\Incomes;

use App\Models\Income;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class IncomePage extends Component
{
    use WithPagination;

    #[Title('Pendapatan')]

    // Basic properties with proper initialization
    public $tanggal = '';
    public $produk = '';
    public $jumlah_terjual = 0;
    public $harga_satuan = 0;
    public $jumlah = 0;
    public $showModal = false;
    public $isEdit = false;
    public $income_id = null;
    public $filterMonth = '';
    public $filterYear = '';
    public $perPage = 10;

    // Array properties with proper initialization
    public array $incomes = [];
    public array $monthlyTotals = [];

    protected $paginationTheme = 'bootstrap';

    // Perbaikan: Ubah menjadi array property seperti di expenditures
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

    protected function rules()
    {
        return [
            'tanggal' => 'required|date',
            'produk' => 'required|string|max:255',
            'jumlah_terjual' => 'required|integer|min:1',
            'harga_satuan' => 'required|numeric|min:0',
        ];
    }

    protected function messages()
    {
        return [
            'tanggal.required' => 'Tanggal wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'produk.required' => 'Nama produk/jasa wajib diisi.',
            'produk.max' => 'Nama produk/jasa maksimal 255 karakter.',
            'jumlah_terjual.required' => 'Jumlah terjual wajib diisi.',
            'jumlah_terjual.integer' => 'Jumlah terjual harus berupa angka.',
            'jumlah_terjual.min' => 'Jumlah terjual minimal 1.',
            'harga_satuan.required' => 'Harga satuan wajib diisi.',
            'harga_satuan.numeric' => 'Harga satuan harus berupa angka.',
            'harga_satuan.min' => 'Harga satuan tidak boleh kurang dari 0.',
        ];
    }

    public function getMonthNameProperty()
    {
        // Perbaikan: Sederhanakan seperti di expenditures
        return isset($this->monthNames[(int) $this->filterMonth])
            ? $this->monthNames[(int) $this->filterMonth]
            : '';
    }

    public function getMaxDateProperty()
    {
        // Perbaikan: Sederhanakan seperti di expenditures
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
        // Perbaikan: Sederhanakan seperti di expenditures
        if (!$this->filterMonth || !$this->filterYear) {
            return '';
        }

        return sprintf('%04d-%02d-01', $this->filterYear, $this->filterMonth);
    }

    public function mount()
    {
        try {
            $this->resetFormData();
            $this->loadMonthlyTotals();
        } catch (\Exception $e) {
            Log::error('Error in mount: ' . $e->getMessage());
            $this->resetFormData();
        }
    }

    private function resetFormData()
    {
        $this->incomes = [];
        $this->jumlah = 0;
        $this->monthlyTotals = [];
        $this->filterMonth = '';
        $this->filterYear = '';
        $this->resetInput();
    }

    // Perbaikan: Ubah nama method dan logika seperti di expenditures
    public function loadIncomes()
    {
        try {
            if (!Auth::check()) {
                $this->incomes = [];
                $this->jumlah = 0;
                return;
            }

            if (!$this->filterMonth || !$this->filterYear) {
                $this->incomes = [];
                $this->jumlah = 0;
                return;
            }

            if (!is_numeric($this->filterMonth) || !is_numeric($this->filterYear)) {
                $this->incomes = [];
                $this->jumlah = 0;
                return;
            }

            $month = (int) $this->filterMonth;
            $year = (int) $this->filterYear;

            // Hitung total pendapatan dengan selectRaw untuk menangani null
            $totalQuery = Income::where('user_id', Auth::id())
                ->whereMonth('tanggal', $month)
                ->whereYear('tanggal', $year)
                ->selectRaw('COALESCE(SUM(jumlah_terjual * harga_satuan), 0) as total_pendapatan')
                ->first();

            $this->jumlah = $totalQuery->total_pendapatan ?? 0;

            // Ambil data incomes dan konversi ke array
            $this->incomes = Income::where('user_id', Auth::id())
                ->whereMonth('tanggal', $month)
                ->whereYear('tanggal', $year)
                ->orderBy('tanggal', 'desc')
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            $this->incomes = [];
            $this->jumlah = 0;

            Log::error('Error in loadIncomes: ' . $e->getMessage(), [
                'filterMonth' => $this->filterMonth,
                'filterYear' => $this->filterYear,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            session()->flash('error', 'Terjadi kesalahan saat memuat data. Silakan coba lagi.');
        }
    }

    // Perbaikan: Buat method terpisah untuk pagination seperti di expenditures
    public function getPaginatedIncomes()
    {
        if (!Auth::check() || !$this->filterMonth || !$this->filterYear) {
            // Return query builder dengan where yang tidak akan pernah match
            return Income::where('id', 0)->paginate($this->perPage);
        }

        if (!is_numeric($this->filterMonth) || !is_numeric($this->filterYear)) {
            return Income::where('id', 0)->paginate($this->perPage);
        }

        try {
            return Income::where('user_id', Auth::id())
                ->whereMonth('tanggal', (int)$this->filterMonth)
                ->whereYear('tanggal', (int)$this->filterYear)
                ->orderBy('tanggal', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate($this->perPage);
        } catch (\Exception $e) {
            Log::error('Error in getPaginatedIncomes: ' . $e->getMessage());
            return Income::where('id', 0)->paginate($this->perPage);
        }
    }

    public function loadMonthlyTotals()
    {
        try {
            $this->monthlyTotals = [];

            if (!Auth::check()) {
                Log::info('No auth user, monthlyTotals set to empty array');
                return;
            }

            $query = Income::where('user_id', Auth::id())
                ->whereNotNull('tanggal');

            if (!empty($this->filterMonth) && is_numeric($this->filterMonth)) {
                $query->whereMonth('tanggal', $this->filterMonth);
            }
            if (!empty($this->filterYear) && is_numeric($this->filterYear)) {
                $query->whereYear('tanggal', $this->filterYear);
            }

            $incomes = $query->orderBy('tanggal', 'desc')->get();

            if ($incomes->isEmpty()) {
                Log::info('No income data found for selected filters, monthlyTotals set to empty array', [
                    'filterMonth' => $this->filterMonth ?? 'null',
                    'filterYear' => $this->filterYear ?? 'null',
                ]);
                return;
            }

            $grouped = [];
            foreach ($incomes as $income) {
                if (!$income || empty($income->tanggal)) {
                    continue;
                }

                try {
                    $date = $income->tanggal instanceof Carbon ? $income->tanggal : Carbon::parse($income->tanggal);
                    $key = $date->format('Y-m');

                    if (!isset($grouped[$key])) {
                        $grouped[$key] = 0;
                    }

                    $jumlahTerjual = (float) ($income->jumlah_terjual ?? 0);
                    $hargaSatuan = (float) ($income->harga_satuan ?? 0);

                    if ($jumlahTerjual >= 0 && $hargaSatuan >= 0) {
                        $grouped[$key] += $jumlahTerjual * $hargaSatuan;
                    }
                } catch (\Exception $e) {
                    Log::error('Error processing income item: ' . $e->getMessage(), [
                        'income_id' => $income->id ?? 'unknown',
                    ]);
                    continue;
                }
            }

            krsort($grouped);
            $this->monthlyTotals = $grouped;

            Log::info('Monthly totals loaded successfully', ['monthlyTotals' => $this->monthlyTotals]);
        } catch (\Exception $e) {
            Log::error('Error loading monthly totals: ' . $e->getMessage(), [
                'user_id' => Auth::id() ?? 'not_logged_in',
                'filterMonth' => $this->filterMonth ?? 'null',
                'filterYear' => $this->filterYear ?? 'null',
            ]);
            $this->monthlyTotals = [];
        }
    }

    public function openModal()
    {
        try {
            if (!Auth::check()) {
                session()->flash('error', 'Anda harus login terlebih dahulu.');
                return;
            }

            if (!$this->filterMonth || !$this->filterYear) {
                session()->flash('error', 'Silakan pilih bulan dan tahun terlebih dahulu.');
                return;
            }

            $this->resetInput();

            // Perbaikan: Sederhanakan seperti di expenditures
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
        } catch (\Exception $e) {
            Log::error('Error opening modal: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat membuka form.');
        }
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
                'produk' => trim((string) $this->produk),
                'jumlah_terjual' => (int) $this->jumlah_terjual,
                'harga_satuan' => (float) $this->harga_satuan,
            ];

            if ($this->isEdit && $this->income_id) {
                $income = Income::where('user_id', Auth::id())->findOrFail($this->income_id);
                $income->user_id = $data['user_id'];
                $income->tanggal = $data['tanggal'];
                $income->produk = $data['produk'];
                $income->jumlah_terjual = $data['jumlah_terjual'];
                $income->harga_satuan = $data['harga_satuan'];
                $income->save();

                session()->flash('message', 'Data berhasil diperbarui!');
            } else {
                $income = new Income;
                $income->user_id = $data['user_id'];
                $income->tanggal = $data['tanggal'];
                $income->produk = $data['produk'];
                $income->jumlah_terjual = $data['jumlah_terjual'];
                $income->harga_satuan = $data['harga_satuan'];
                $income->save();

                session()->flash('message', 'Data berhasil ditambahkan!');
            }

            $this->closeModal();
            $this->loadIncomes();
            $this->loadMonthlyTotals();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Income not found: ' . $e->getMessage(), [
                'income_id' => $this->income_id,
                'user_id' => Auth::id(),
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

            $income = Income::where('user_id', Auth::id())->findOrFail($id);

            $this->income_id = $income->id;

            // Perbaikan: Sederhanakan seperti di expenditures
            if ($income->tanggal instanceof \Carbon\Carbon) {
                $this->tanggal = $income->tanggal->format('Y-m-d');
            } else {
                $this->tanggal = Carbon::parse((string) $income->tanggal)->format('Y-m-d');
            }

            $this->produk = (string) ($income->produk ?? '');
            $this->jumlah_terjual = (int) ($income->jumlah_terjual ?? 0);
            $this->harga_satuan = (float) ($income->harga_satuan ?? 0);
            $this->isEdit = true;
            $this->showModal = true;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Income not found for edit: ' . $e->getMessage(), [
                'id' => $id,
                'user_id' => Auth::id(),
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

            $income = Income::where('user_id', Auth::id())->findOrFail($id);
            $income->delete();

            $this->loadIncomes();
            $this->loadMonthlyTotals();
            session()->flash('message', 'Data berhasil dihapus!');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Income not found for delete: ' . $e->getMessage(), [
                'id' => $id,
                'user_id' => Auth::id(),
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

            // Get all incomes for export (not just paginated ones)
            $incomes = Income::where('user_id', Auth::id())
                ->whereMonth('tanggal', (int)$this->filterMonth)
                ->whereYear('tanggal', (int)$this->filterYear)
                ->orderBy('tanggal', 'desc')
                ->get();

            if ($incomes->isEmpty()) {
                session()->flash('error', 'Tidak ada data untuk di-export.');
                return;
            }

            // Nama file dengan format: pendapatan_YYYY-MM.csv
            $monthName = $this->monthNames[(int)$this->filterMonth];
            $fileName = sprintf('pendapatan_%s_%s.csv', $monthName, $this->filterYear);

            // Header CSV
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
                'Pragma' => 'public',
            ];

            // Callback untuk generate CSV
            $callback = function () use ($incomes) {
                $file = fopen('php://output', 'w');

                // Add BOM untuk support Unicode di Excel
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

                // Header kolom
                fputcsv($file, [
                    'No',
                    'Tanggal',
                    'Produk/Jasa',
                    'Jumlah Terjual',
                    'Harga Satuan',
                    'Total Pendapatan',
                    'Tanggal Dibuat',
                    'Tanggal Diupdate'
                ], ';');

                // Data rows
                $no = 1;
                $grandTotal = 0;
                foreach ($incomes as $income) {
                    if (!$income) continue;

                    $jumlahTerjual = (float) ($income->jumlah_terjual ?? 0);
                    $hargaSatuan = (float) ($income->harga_satuan ?? 0);
                    $total = $jumlahTerjual * $hargaSatuan;
                    $grandTotal += $total;

                    $tanggal = Carbon::parse($income->tanggal)->format('d/m/Y');
                    $createdAt = Carbon::parse($income->created_at)->format('d/m/Y H:i:s');
                    $updatedAt = Carbon::parse($income->updated_at)->format('d/m/Y H:i:s');

                    fputcsv($file, [
                        $no++,
                        $tanggal,
                        (string) ($income->produk ?? ''),
                        number_format($jumlahTerjual, 0, ',', '.'),
                        number_format($hargaSatuan, 0, ',', '.'),
                        number_format($total, 0, ',', '.'),
                        $createdAt,
                        $updatedAt
                    ], ';');
                }

                // Row total
                fputcsv($file, [], ';'); // Empty row
                fputcsv($file, [
                    '',
                    '',
                    '',
                    '',
                    'TOTAL:',
                    number_format($grandTotal, 0, ',', '.'),
                    '',
                    ''
                ], ';');

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
            return;
        }
    }

    public function clearFilters()
    {
        $this->filterMonth = '';
        $this->filterYear = '';
        $this->incomes = [];
        $this->jumlah = 0;
        $this->resetInput();
        $this->resetPage(); // Reset pagination saat clear filter
    }

    public function resetInput()
    {
        $this->reset([
            'tanggal',
            'produk',
            'jumlah_terjual',
            'harga_satuan',
            'income_id',
            'isEdit'
        ]);

        $this->resetValidation();
    }

    public function updatedFilterMonth()
    {
        $this->resetInput();
        $this->resetPage(); // Reset pagination saat ganti filter
        $this->loadIncomes();
        $this->loadMonthlyTotals();
    }

    public function updatedFilterYear()
    {
        $this->resetInput();
        $this->resetPage(); // Reset pagination saat ganti filter
        $this->loadIncomes();
        $this->loadMonthlyTotals();
    }

    // Tambahkan method untuk mengubah jumlah data per halaman
    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function updatedJumlahTerjual()
    {
        $this->validateOnly('jumlah_terjual');
    }

    public function updatedHargaSatuan()
    {
        $this->validateOnly('harga_satuan');
    }

    public function updatedProduk()
    {
        $this->validateOnly('produk');
    }

    public function updatedTanggal()
    {
        $this->validateOnly('tanggal');
    }

    public function getIncomesCollection()
    {
        return collect($this->incomes);
    }

    public function getAvailableYears()
    {
        $currentYear = now()->year;
        $startYear = 2020;

        return range($currentYear, $startYear);
    }

    public function render()
    {
        try {
            // Dapatkan data yang dipaginate untuk ditampilkan
            $paginatedIncomes = $this->getPaginatedIncomes();

            return view('livewire.incomes.income-page', [
                'paginatedIncomes' => $paginatedIncomes,
                'monthlyTotals' => $this->monthlyTotals,
                'monthNames' => $this->monthNames,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in render method: ' . $e->getMessage());
            return view('livewire.incomes.income-page', [
                'paginatedIncomes' => Income::where('id', 0)->paginate($this->perPage),
                'monthlyTotals' => [],
                'monthNames' => $this->monthNames,
            ]);
        }
    }
}