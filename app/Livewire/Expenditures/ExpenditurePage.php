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
    public $showModal = false;
    public $isEdit = false;
    public $expenditure_id;
    public array $monthlyTotals = [];
    public $filterMonth = '';
    public $filterYear = '';
    public $perPage = 10;
    public $totalExpenditure = 0; // Total pengeluaran untuk periode yang dipilih
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

    protected function rules()
    {
        $rules = [
            'tanggal' => 'required|date',
            'keterangan' => 'required|string|max:255',
            'jumlah' => 'required|numeric|min:0',
        ];

        // Tambahkan validasi tanggal yang lebih ketat jika filter sudah dipilih
        if ($this->filterMonth && $this->filterYear) {
            $minDate = sprintf('%04d-%02d-01', (int)$this->filterYear, (int)$this->filterMonth);
            $maxDate = Carbon::create((int)$this->filterYear, (int)$this->filterMonth, 1)->endOfMonth()->format('Y-m-d');
            $rules['tanggal'] .= "|after_or_equal:{$minDate}|before_or_equal:{$maxDate}";
        }

        return $rules;
    }

    protected $messages = [
        'tanggal.required' => 'Tanggal wajib diisi.',
        'tanggal.date' => 'Format tanggal tidak valid.',
        'tanggal.after_or_equal' => 'Tanggal harus berada dalam bulan dan tahun yang dipilih.',
        'tanggal.before_or_equal' => 'Tanggal harus berada dalam bulan dan tahun yang dipilih.',
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
        $this->monthlyTotals = [];
        $this->filterMonth = '';
        $this->filterYear = '';
        $this->totalExpenditure = 0;
        $this->resetInput();
    }

    // Method yang dipanggil saat komponen di-boot
    public function boot()
    {
        // Pastikan filter tersimpan di session saat komponen di-boot
        if ($this->filterMonth && $this->filterYear) {
            session(['expenditure_filter_month' => $this->filterMonth]);
            session(['expenditure_filter_year' => $this->filterYear]);
        }
    }

    // Method yang dipanggil saat komponen di-boot
    public function booted()
    {
        // Pastikan filter tersimpan di session saat komponen di-boot
        if ($this->filterMonth && $this->filterYear) {
            session(['expenditure_filter_month' => $this->filterMonth]);
            session(['expenditure_filter_year' => $this->filterYear]);
        }
    }

    // Method yang dipanggil setelah komponen di-hydrate (setelah navigasi)
    public function hydrate()
    {
        // Pastikan filter tersimpan di session
        if ($this->filterMonth && $this->filterYear) {
            session(['expenditure_filter_month' => $this->filterMonth]);
            session(['expenditure_filter_year' => $this->filterYear]);
        }
    }

    // Method yang dipanggil sebelum komponen di-dehydrate (sebelum navigasi)
    public function dehydrate()
    {
        // Pastikan filter tersimpan di session sebelum navigasi
        if ($this->filterMonth && $this->filterYear) {
            session(['expenditure_filter_month' => $this->filterMonth]);
            session(['expenditure_filter_year' => $this->filterYear]);
        }
    }

    public function loadMonthlyTotals()
    {
        try {
            if (!Auth::check()) {
                $this->monthlyTotals = [];
                return;
            }

            // Jika filter kosong, tetap muat data untuk ringkasan 6 bulan terakhir
            // tapi jangan update total pengeluaran

            // Gunakan model dengan global scope - tidak perlu where('user_id', Auth::id())
            $query = Expenditure::query()
                ->whereNotNull('tanggal');

            // Filter berdasarkan tahun jika sudah dipilih
            if (!empty($this->filterYear) && is_numeric($this->filterYear)) {
                $query->whereYear('tanggal', (int)$this->filterYear);
                
                // Jangan filter berdasarkan bulan di sini agar tetap menampilkan semua bulan dalam tahun
                // Total untuk bulan tertentu akan dihitung di updateTotalExpenditure()
            }

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

            // Update total pengeluaran jika bulan dan tahun sudah dipilih
            if (!empty($this->filterMonth) && !empty($this->filterYear)) {
                $this->updateTotalExpenditure();
            }

        } catch (\Exception $e) {
            $this->monthlyTotals = [];
            Log::error('Error loading monthly totals: ' . $e->getMessage(), [
                'filterYear' => $this->filterYear,
                'filterMonth' => $this->filterMonth,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    // Method untuk memuat data pengeluaran sesuai periode yang dipilih
    public function loadExpenditures()
    {
        try {
            if (!Auth::check()) {
                $this->totalExpenditure = 0;
                return;
            }

            if (!$this->filterMonth || !$this->filterYear) {
                $this->totalExpenditure = 0;
                return;
            }

            if (!is_numeric($this->filterMonth) || !is_numeric($this->filterYear)) {
                $this->totalExpenditure = 0;
                return;
            }

            $this->updateTotalExpenditure();

        } catch (\Exception $e) {
            $this->totalExpenditure = 0;

            Log::error('Error in loadExpenditures: ' . $e->getMessage(), [
                'filterMonth' => $this->filterMonth,
                'filterYear' => $this->filterYear,
                'trace' => $e->getTraceAsString()
            ]);

            session()->flash('error', 'Terjadi kesalahan saat memuat data. Silakan coba lagi.');
        }
    }

    // Method untuk refresh data setelah operasi CRUD
    public function refreshData()
    {
        
        // Pastikan filter tersimpan di session sebelum refresh
        if ($this->filterMonth && $this->filterYear) {
            session(['expenditure_filter_month' => $this->filterMonth]);
            session(['expenditure_filter_year' => $this->filterYear]);
        }
        
        $this->loadMonthlyTotals();
        $this->loadExpenditures();
    }

    // Method baru untuk menghitung total pengeluaran yang konsisten
    private function updateTotalExpenditure()
    {
        try {
            $month = (int) $this->filterMonth;
            $year = (int) $this->filterYear;

            // Hitung total pengeluaran untuk periode yang dipilih
            // Gunakan query builder dengan global scope
            $query = Expenditure::query()
                ->whereMonth('tanggal', $month)
                ->whereYear('tanggal', $year);

            $totalExpenditure = $query->sum('jumlah');

            $this->totalExpenditure = (float) $totalExpenditure;


        } catch (\Exception $e) {
            $this->totalExpenditure = 0;
            Log::error('Error in updateTotalExpenditure: ' . $e->getMessage(), [
                'filterMonth' => $this->filterMonth,
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
            // Pastikan tanggal default selalu sesuai dengan periode yang dipilih
            $today = now();
            $selectedMonth = (int) $this->filterMonth;
            $selectedYear = (int) $this->filterYear;
            
            // Jika periode yang dipilih adalah bulan dan tahun saat ini, gunakan tanggal hari ini
            if ($today->month == $selectedMonth && $today->year == $selectedYear) {
                $this->tanggal = $today->format('Y-m-d');
            } else {
                // Jika bukan bulan/tahun saat ini, gunakan tanggal 1 dari bulan yang dipilih
                $this->tanggal = sprintf('%04d-%02d-01', $selectedYear, $selectedMonth);
            }
        } catch (\Exception $e) {
            $this->tanggal = sprintf('%04d-%02d-01', (int)$this->filterYear, (int)$this->filterMonth);
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

            // Validasi tanggal harus sesuai dengan periode yang dipilih
            $selectedDate = Carbon::parse($this->tanggal, config('app.timezone'));
            if ($selectedDate->month != (int)$this->filterMonth || $selectedDate->year != (int)$this->filterYear) {
                $this->addError('tanggal', 'Tanggal harus sesuai dengan bulan dan tahun yang dipilih.');
                return;
            }

            $data = [
                'user_id' => Auth::id(), // Tambahkan user_id dari user yang sedang login
                'tanggal' => $this->tanggal,
                'keterangan' => trim($this->keterangan),
                'jumlah' => (float) $this->jumlah,
            ];

            if ($this->isEdit && $this->expenditure_id) {
                $expenditure = Expenditure::findOrFail($this->expenditure_id);
                
                // Validasi tanggal saat edit juga
                $oldDate = Carbon::parse($expenditure->tanggal);
                if ($oldDate->month != (int)$this->filterMonth || $oldDate->year != (int)$this->filterYear) {
                    $this->addError('tanggal', 'Data yang diedit harus berada dalam periode yang dipilih.');
                    return;
                }
                
                $expenditure->update($data);
                session()->flash('message', 'Data berhasil diperbarui!');
            } else {
                Expenditure::create($data);
                session()->flash('message', 'Data berhasil ditambahkan!');
            }

            $this->closeModal();
            $this->refreshData();
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Expenditure not found: ' . $e->getMessage(), [
                'expenditure_id' => $this->expenditure_id
            ]);
            session()->flash('error', 'Data tidak ditemukan atau Anda tidak memiliki akses.');
        } catch (\Exception $e) {
            Log::error('Error in save method: ' . $e->getMessage(), [
                'data' => $data ?? null,
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

            $expenditure = Expenditure::findOrFail($id);
            
            // Double check: pastikan expenditure milik user yang sedang login
            if ($expenditure->user_id !== Auth::id()) {
                session()->flash('error', 'Anda tidak memiliki akses ke data ini.');
                return;
            }

            // Validasi bahwa data yang diedit berada dalam periode yang dipilih
            $expenditureDate = Carbon::parse($expenditure->tanggal);
            if ($expenditureDate->month != (int)$this->filterMonth || $expenditureDate->year != (int)$this->filterYear) {
                session()->flash('error', 'Data yang diedit tidak berada dalam periode yang dipilih. Silakan pilih periode yang sesuai terlebih dahulu.');
                return;
            }

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
                'id' => $id
            ]);
            session()->flash('error', 'Data tidak ditemukan atau Anda tidak memiliki akses.');
        } catch (\Exception $e) {
            Log::error('Error in edit method: ' . $e->getMessage(), [
                'id' => $id,
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

            $expenditure = Expenditure::findOrFail($id);
            
            // Double check: pastikan expenditure milik user yang sedang login
            if ($expenditure->user_id !== Auth::id()) {
                session()->flash('error', 'Anda tidak memiliki akses ke data ini.');
                return;
            }
            
            $expenditure->delete();
            
            $this->refreshData();
            session()->flash('message', 'Data berhasil dihapus!');
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Expenditure not found for delete: ' . $e->getMessage(), [
                'id' => $id
            ]);
            session()->flash('error', 'Data tidak ditemukan atau Anda tidak memiliki akses.');
        } catch (\Exception $e) {
            Log::error('Error in delete method: ' . $e->getMessage(), [
                'id' => $id,
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

            $expenditures = Expenditure::whereMonth('tanggal', (int)$this->filterMonth)
                ->whereYear('tanggal', (int)$this->filterYear)
                ->orderBy('tanggal', 'desc')
                ->get();

            if ($expenditures->isEmpty()) {
                session()->flash('error', 'Tidak ada data untuk di-export.');
                return;
            }

            $monthName = $this->monthNames[(int)$this->filterMonth];
            $fileName = sprintf('Laporan_Pengeluaran_%s_%s.csv', $monthName, $this->filterYear);

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ];

            $callback = function() use ($expenditures, $monthName) {
                $file = fopen('php://output', 'w');
                
                // Add BOM to support Unicode in Excel
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

                // Header informasi laporan
                fputcsv($file, ['LAPORAN PENGELUARAN']);
                fputcsv($file, ['Periode: ' . $monthName . ' ' . $this->filterYear]);
                fputcsv($file, ['Tanggal Export: ' . now()->format('d F Y H:i:s')]);
                fputcsv($file, []); // Empty row

                // Column headers dengan format yang lebih rapi
                fputcsv($file, [
                    'No',
                    'Tanggal',
                    'Keterangan',
                    'Jumlah Pengeluaran (Rp)',
                    'Kategori'
                ]);

                $no = 1;
                $grandTotal = 0;
                $kategorisasi = [];
                
                foreach ($expenditures as $expenditure) {
                    $jumlah = (float) $expenditure->jumlah;
                    $grandTotal += $jumlah;
                    
                    // Kategorisasi sederhana berdasarkan keterangan
                    $kategori = $this->kategorisasiPengeluaran($expenditure->keterangan);
                    if (isset($kategorisasi[$kategori])) {
                        $kategorisasi[$kategori] += $jumlah;
                    } else {
                        $kategorisasi[$kategori] = $jumlah;
                    }

                    fputcsv($file, [
                        $no++,
                        Carbon::parse($expenditure->tanggal)->format('d/m/Y'),
                        (string) $expenditure->keterangan,
                        number_format($jumlah, 0, ',', '.'),
                        $kategori
                    ]);
                }

                // Summary section
                fputcsv($file, []); // Empty row
                fputcsv($file, ['RINGKASAN LAPORAN']);
                fputcsv($file, []); // Empty row
                
                fputcsv($file, ['Total Pengeluaran:', number_format($grandTotal, 0, ',', '.')]);
                fputcsv($file, ['Jumlah Transaksi:', $expenditures->count()]);
                fputcsv($file, ['Rata-rata per Transaksi:', number_format($grandTotal / $expenditures->count(), 0, ',', '.')]);
                
                // Breakdown by category
                if (!empty($kategorisasi)) {
                    fputcsv($file, []); // Empty row
                    fputcsv($file, ['BREAKDOWN PER KATEGORI']);
                    fputcsv($file, []); // Empty row
                    
                    foreach ($kategorisasi as $kategori => $total) {
                        $persentase = ($total / $grandTotal) * 100;
                        fputcsv($file, [
                            $kategori . ':', 
                            number_format($total, 0, ',', '.') . ' (' . number_format($persentase, 1, ',', '.') . '%)'
                        ]);
                    }
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            Log::error('Error in exportCSV method: ' . $e->getMessage(), [
                'filterMonth' => $this->filterMonth,
                'filterYear' => $this->filterYear,
                'trace' => $e->getTraceAsString()
            ]);
            
            session()->flash('error', 'Terjadi kesalahan saat export data. Silakan coba lagi.');
        }
    }

    /**
     * Kategorisasi pengeluaran berdasarkan keterangan
     */
    private function kategorisasiPengeluaran($keterangan)
    {
        $keterangan = strtolower($keterangan);
        
        // Bahan baku dan inventori
        if (preg_match('/\b(beli|bahan|stock|stok|inventory|inventori|baku|material)\b/', $keterangan)) {
            return 'Bahan Baku & Inventori';
        }
        
        // Operasional
        if (preg_match('/\b(listrik|air|gas|internet|telepon|sewa|rent|operasional|maintenance|perawatan)\b/', $keterangan)) {
            return 'Operasional';
        }
        
        // Transport dan logistik
        if (preg_match('/\b(transport|ongkir|kirim|bensin|solar|ojek|grab|gojek|ekspedisi|logistik)\b/', $keterangan)) {
            return 'Transport & Logistik';
        }
        
        // Marketing dan promosi
        if (preg_match('/\b(iklan|promosi|marketing|sosmed|facebook|instagram|google|ads|banner)\b/', $keterangan)) {
            return 'Marketing & Promosi';
        }
        
        // Peralatan dan perlengkapan
        if (preg_match('/\b(alat|peralatan|perlengkapan|equipment|mesin|komputer|printer)\b/', $keterangan)) {
            return 'Peralatan & Perlengkapan';
        }
        
        // Administrasi
        if (preg_match('/\b(admin|administrasi|pajak|retribusi|perizinan|notaris|legal|bank|transfer)\b/', $keterangan)) {
            return 'Administrasi & Pajak';
        }
        
        return 'Lainnya';
    }

    public function clearFilters()
    {
        
        $this->filterMonth = '';
        $this->filterYear = '';
        
        // Hapus filter dari session
        session()->forget(['expenditure_filter_month', 'expenditure_filter_year']);
        
        $this->resetInput();
        $this->resetPage();
        $this->totalExpenditure = 0;
        $this->loadMonthlyTotals();
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
        
        // Simpan filter ke session
        session(['expenditure_filter_month' => $this->filterMonth]);
        
        $this->resetPage();
        $this->loadMonthlyTotals();
        $this->loadExpenditures();
    }

    public function updatedFilterYear()
    {
        
        // Simpan filter ke session
        session(['expenditure_filter_year' => $this->filterYear]);
        
        $this->resetPage();
        $this->loadMonthlyTotals();
        $this->loadExpenditures();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    // Method yang dipanggil setiap kali property berubah
    public function updated($property)
    {
        // Jika filter berubah, simpan ke session
        if (in_array($property, ['filterMonth', 'filterYear'])) {
            if ($this->filterMonth && $this->filterYear) {
                session(['expenditure_filter_month' => $this->filterMonth]);
                session(['expenditure_filter_year' => $this->filterYear]);
            }
        }
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

    public function getAvailableYears()
    {
        $currentYear = now()->year;
        $startYear = 2020;
        
        return range($currentYear + 1, $startYear);
    }

    // Method untuk memuat filter dari session
    public function loadFiltersFromSession()
    {
        $this->filterMonth = session('expenditure_filter_month', '');
        $this->filterYear = session('expenditure_filter_year', '');
        
        if ($this->filterMonth && $this->filterYear) {
            $this->refreshData();
        }
    }

    // Method untuk mengatur filter default dan menyimpannya ke session
    public function setDefaultFilters()
    {
        $this->filterMonth = now()->month;
        $this->filterYear = now()->year;
        
        // Simpan ke session
        session(['expenditure_filter_month' => $this->filterMonth]);
        session(['expenditure_filter_year' => $this->filterYear]);
        
        $this->refreshData();
    }

    public function render()
    {
        // Pastikan filter tersimpan di session sebelum render
        if ($this->filterMonth && $this->filterYear) {
            session(['expenditure_filter_month' => $this->filterMonth]);
            session(['expenditure_filter_year' => $this->filterYear]);
        }

        $paginatedExpenditures = collect([]);

        if (Auth::check() && $this->filterMonth && $this->filterYear) {
            try {
                $month = (int) $this->filterMonth;
                $year = (int) $this->filterYear;

                // Query untuk data yang sesuai periode
                // Gunakan query builder dengan global scope
                $query = Expenditure::query()
                    ->whereMonth('tanggal', $month)
                    ->whereYear('tanggal', $year);

                $paginatedExpenditures = $query->orderBy('tanggal', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->paginate($this->perPage);

            } catch (\Exception $e) {
                session()->flash('error', 'Terjadi kesalahan saat memuat data.');
                $paginatedExpenditures = new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage);
            }
        } else {
            $paginatedExpenditures = new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage);
    }
        
        return view('livewire.expenditures.expenditure-page', [
            'paginatedExpenditures' => $paginatedExpenditures,
            'total' => $this->totalExpenditure
        ]);
    }
}
