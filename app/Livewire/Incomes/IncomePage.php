<?php

namespace App\Livewire\Incomes;

use App\Models\Income;
use App\Models\Product;
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
    public $produk = ''; // This will be deprecated, but kept for now
    public $product_id = null;
    public $jumlah_terjual = 0;
    public $harga_satuan = 0;
    public $biaya_per_unit = 0;
    public $desired_margin = 30;
    public $jumlah = 0;
    public $totalLaba = 0;
    public $showModal = false;
    public $isEdit = false;
    public $income_id = null;
    public $filterMonth = '';
    public $filterYear = '';
    public $perPage = 10;

    // Array properties with proper initialization
    public array $incomes = [];
    public array $products = [];
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
            'product_id' => 'required|integer|exists:products,id',
            'jumlah_terjual' => 'required|integer|min:1',
            'harga_satuan' => 'required|numeric|min:0',
            'biaya_per_unit' => 'nullable|numeric|min:0',
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
            'biaya_per_unit.numeric' => 'Biaya per unit harus berupa angka.',
            'biaya_per_unit.min' => 'Biaya per unit tidak boleh kurang dari 0.',
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
            $this->products = Product::where('user_id', Auth::id())->orderBy('name')->get()->toArray();
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

            $totals = Income::where('user_id', Auth::id())
                ->whereMonth('tanggal', $month)
                ->whereYear('tanggal', $year)
                ->selectRaw('COALESCE(SUM(total_pendapatan), 0) as total_pendapatan, COALESCE(SUM(laba), 0) as total_laba')
                ->first();

            $this->jumlah = $totals->total_pendapatan ?? 0;
            $this->totalLaba = $totals->total_laba ?? 0;

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

            $product = Product::findOrFail($this->product_id);

            // Check if there is enough stock
            if (!$this->isEdit && $product->quantity < $this->jumlah_terjual) {
                $this->addError('jumlah_terjual', 'Stok tidak mencukupi. Stok tersisa: ' . $product->quantity);
                return;
            }

            $total_pendapatan = (float) $this->harga_satuan * (int) $this->jumlah_terjual;
            $laba = ($this->harga_satuan - $this->biaya_per_unit) * $this->jumlah_terjual;

            $data = [
                'user_id' => Auth::id(),
                'product_id' => $this->product_id,
                'tanggal' => $this->tanggal,
                'produk' => $product->name, // Populate legacy field
                'jumlah_terjual' => (int) $this->jumlah_terjual,
                'harga_satuan' => (float) $this->harga_satuan,
                'biaya_per_unit' => (float) ($this->biaya_per_unit ?? 0),
                'total_pendapatan' => $total_pendapatan,
                'laba' => $laba,
            ];

            if ($this->isEdit && $this->income_id) {
                $income = Income::where('user_id', Auth::id())->findOrFail($this->income_id);
                
                // Re-add old quantity to stock before updating
                $old_product = Product::find($income->product_id);
                if($old_product) {
                    $old_product->quantity += $income->jumlah_terjual;
                    $old_product->save();
                }

                $income->update($data);
                
                // Deduct new quantity from stock
                $product->quantity -= (int) $this->jumlah_terjual;
                $product->save();

                session()->flash('message', 'Data berhasil diperbarui!');
            } else {
                Income::create($data);

                // Deduct from stock
                $product->quantity -= (int) $this->jumlah_terjual;
                $product->save();

                session()->flash('message', 'Data berhasil ditambahkan!');
            }

            $this->closeModal();
            $this->loadIncomes();
            $this->loadMonthlyTotals();
            $this->dispatch('loadProductAnalysis', ['month' => $this->filterMonth, 'year' => $this->filterYear]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Income or Product not found: ' . $e->getMessage(), [
                'income_id' => $this->income_id,
                'product_id' => $this->product_id,
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
            $this->product_id = $income->product_id;

            // Perbaikan: Sederhanakan seperti di expenditures
            if ($income->tanggal instanceof \Carbon\Carbon) {
                $this->tanggal = $income->tanggal->format('Y-m-d');
            } else {
                $this->tanggal = Carbon::parse((string) $income->tanggal)->format('Y-m-d');
            }

            $this->produk = (string) ($income->produk ?? '');
            $this->jumlah_terjual = (int) ($income->jumlah_terjual ?? 0);
            $this->harga_satuan = (float) ($income->harga_satuan ?? 0);
            $this->biaya_per_unit = (float) ($income->biaya_per_unit ?? 0);
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
            
            // Restore stock
            if ($income->product_id) {
                $product = Product::find($income->product_id);
                if ($product) {
                    $product->quantity += $income->jumlah_terjual;
                    $product->save();
                }
            }
            
            $income->delete();

            $this->loadIncomes();
            $this->loadMonthlyTotals();
            $this->dispatch('loadProductAnalysis', ['month' => $this->filterMonth, 'year' => $this->filterYear]);
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

            $incomes = Income::where('user_id', Auth::id())
                ->whereMonth('tanggal', (int)$this->filterMonth)
                ->whereYear('tanggal', (int)$this->filterYear)
                ->orderBy('tanggal', 'desc')
                ->get();

            if ($incomes->isEmpty()) {
                session()->flash('error', 'Tidak ada data untuk di-export.');
                return;
            }

            $monthName = $this->monthNames[(int)$this->filterMonth];
            $fileName = sprintf('pendapatan_%s_%s.csv', $monthName, $this->filterYear);

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ];

            $callback = function () use ($incomes) {
                $file = fopen('php://output', 'w');

                // Add BOM to support Unicode in Excel
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

                // Column headers
                fputcsv($file, [
                    'No',
                    'Tanggal',
                    'Produk/Jasa',
                    'Jumlah Terjual',
                    'Harga Satuan',
                    'Biaya per Unit',
                    'Total Pendapatan',
                    'Laba',
                    'Tanggal Dibuat',
                    'Tanggal Diupdate'
                ]);

                $no = 1;
                $grandTotal = 0;
                $totalLaba = 0;
                foreach ($incomes as $income) {
                    if (!$income) continue;

                    $grandTotal += $income->total_pendapatan;
                    $totalLaba += $income->laba;

                    fputcsv($file, [
                        $no++,
                        Carbon::parse($income->tanggal)->format('Y-m-d'),
                        (string) ($income->produk ?? ''),
                        $income->jumlah_terjual,
                        $income->harga_satuan,
                        $income->biaya_per_unit,
                        $income->total_pendapatan,
                        $income->laba,
                        Carbon::parse($income->created_at)->format('Y-m-d H:i:s'),
                        Carbon::parse($income->updated_at)->format('Y-m-d H:i:s')
                    ]);
                }

                // Total row
                fputcsv($file, []); // Empty row
                fputcsv($file, [
                    '', '', '', '', '', 'TOTAL:',
                    $grandTotal,
                    $totalLaba,
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
            'product_id',
            'jumlah_terjual',
            'harga_satuan',
            'biaya_per_unit',
            'desired_margin',
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
        $this->dispatch('loadProductAnalysis', ['month' => $this->filterMonth, 'year' => $this->filterYear]);
    }

    public function updatedFilterYear()
    {
        $this->resetInput();
        $this->resetPage(); // Reset pagination saat ganti filter
        $this->loadIncomes();
        $this->loadMonthlyTotals();
        $this->dispatch('loadProductAnalysis', ['month' => $this->filterMonth, 'year' => $this->filterYear]);
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