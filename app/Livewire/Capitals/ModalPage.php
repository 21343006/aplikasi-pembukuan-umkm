<?php

namespace App\Livewire\Capitals;

use App\Models\Capital;
use Livewire\Attributes\Title;
use Livewire\Component;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class ModalPage extends Component
{
    #[Title('Pengelolaan Modal')]

    // Properties untuk form
    public $nama;
    public $tanggal;
    public $keperluan;
    public $keterangan;
    public $nominal;
    public $jenis = 'masuk'; // masuk atau keluar

    // Properties untuk UI state
    public $showModal = false;
    public $isEdit = false;
    public $editId = null;

    // Properties untuk filter
    public $filterJenis = '';

    // Properties untuk data
    public array $capitals = [];
    public $totalMasuk = 0;
    public $totalKeluar = 0;
    public $saldo = 0;
    public $totalTransaksi = 0;

    public $monthName;
    public $filterYear;

    // Array nama bulan dalam bahasa Indonesia
    public array $monthNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];

    // Cache untuk pengecekan kolom
    private $columnCache = [];

    /**
     * Validasi rules yang dinamis berdasarkan keberadaan kolom
     */
    public function getRules()
    {
        $rules = [
            'tanggal'    => 'required|date|before_or_equal:today',
            'nominal'    => 'required|numeric|min:1|max:999999999999',
        ];

        // Tambahkan validasi untuk kolom opsional jika ada
        if ($this->hasColumn('keperluan')) {
            $rules['keperluan'] = 'nullable|string|max:255';
        }

        if ($this->hasColumn('keterangan')) {
            $rules['keterangan'] = 'nullable|string|max:500';
        }

        if ($this->hasColumn('jenis')) {
            $rules['jenis'] = 'required|in:masuk,keluar';
        }

        return $rules;
    }

    /**
     * Pesan validasi yang lebih user friendly
     */
    protected $messages = [
        'tanggal.required' => 'Tanggal wajib diisi.',
        'tanggal.date' => 'Format tanggal tidak valid.',
        'tanggal.before_or_equal' => 'Tanggal tidak boleh lebih dari hari ini.',
        'nominal.required' => 'Nominal wajib diisi.',
        'nominal.numeric' => 'Nominal harus berupa angka.',
        'nominal.min' => 'Nominal minimal Rp 1.',
        'nominal.max' => 'Nominal terlalu besar.',
        'keterangan.max' => 'Keterangan maksimal 500 karakter.',
        'keperluan.max' => 'Keperluan maksimal 255 karakter.',
        'jenis.required' => 'Jenis transaksi wajib dipilih.',
        'jenis.in' => 'Jenis transaksi tidak valid.',
    ];

    /**
     * Cek apakah kolom ada di tabel dengan caching
     */
    public function hasColumn($columnName)
    {
        if (!isset($this->columnCache[$columnName])) {
            try {
                $this->columnCache[$columnName] = Capital::hasColumn($columnName);
            } catch (\Exception $e) {
                Log::error("Error checking column {$columnName}: " . $e->getMessage());
                $this->columnCache[$columnName] = false;
            }
        }
        return $this->columnCache[$columnName];
    }

    

    /**
     * Inisialisasi komponen
     */
    public function mount()
    {
        try {
            
            $this->tanggal = now()->format('Y-m-d');

            $now = Carbon::now();
            $this->monthName = $this->monthNames[$now->month];
            $this->filterYear = $now->year;
            
            $this->resetData();
            $this->loadCapitals();

            // Refresh column cache
            Capital::refreshColumnCache();

            Log::info('Modal Page mounted successfully', [
                'user_id' => Auth::check() ? Auth::id() : null,
                'has_jenis_column' => $this->hasColumn('jenis')
            ]);
        } catch (\Exception $e) {
            Log::error('Error mounting Modal Page: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Terjadi kesalahan saat memuat halaman.');
        }
    }

    /**
     * Reset data ke nilai default
     */
    private function resetData()
    {
        $this->capitals = [];
        $this->totalMasuk = 0;
        $this->totalKeluar = 0;
        $this->saldo = 0;
        $this->totalTransaksi = 0;
    }

    /**
     * Load data capitals berdasarkan filter dengan backward compatibility
     */
    public function loadCapitals()
    {
        try {
            if (!Auth::check()) {
                $this->resetData();
                return;
            }

            $query = Capital::where('user_id', Auth::id());

            // Apply filter jenis jika kolom ada dan filter aktif
            if ($this->filterJenis && $this->hasColumn('jenis')) {
                $query->where('jenis', $this->filterJenis);
            }

            $result = $query->orderBy('tanggal', 'desc')
                          ->orderBy('created_at', 'desc')
                          ->get();
            
            $this->capitals = $result->toArray();

            // Calculate totals dengan backward compatibility
            if ($this->hasColumn('jenis')) {
                $this->totalMasuk = $result->where('jenis', 'masuk')->sum('nominal');
                $this->totalKeluar = $result->where('jenis', 'keluar')->sum('nominal');
            } else {
                // Jika kolom jenis belum ada, anggap semua sebagai masuk
                $this->totalMasuk = $result->sum('nominal');
                $this->totalKeluar = 0;
            }

            $this->saldo = $this->totalMasuk - $this->totalKeluar;
            $this->totalTransaksi = $result->count();

        } catch (\Exception $e) {
            $this->resetData();
            Log::error('Error in loadCapitals: ' . $e->getMessage(), [
                'user_id' => Auth::check() ? Auth::id() : null,
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Terjadi kesalahan saat memuat data. Silakan coba lagi.');
        }
    }

    /**
     * Buka modal untuk tambah/edit data
     */
    public function openModal($jenis = 'masuk')
    {
        try {
            if (!Auth::check()) {
                session()->flash('error', 'Anda harus login terlebih dahulu.');
                return;
            }

            $this->showModal = true;
            $this->isEdit = false;
            $this->editId = null;
            $this->jenis = $jenis;

            $this->tanggal = now()->format('Y-m-d');

            $this->resetInput();

            Log::info('Modal opened', [
                'jenis' => $jenis,
                'user_id' => Auth::id(),
                'has_jenis_column' => $this->hasColumn('jenis')
            ]);
        } catch (\Exception $e) {
            Log::error('Error opening modal: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat membuka form.');
        }
    }

    /**
     * Tutup modal dan reset form
     */
    public function closeModal()
    {
        $this->showModal = false;
        $this->isEdit = false;
        $this->editId = null;
        $this->jenis = 'masuk';
        $this->resetInput();
    }

    /**
     * Reset input form
     */
    private function resetInput()
    {
        $this->reset([
            'nama', 'keperluan', 'keterangan', 'nominal'
        ]);
        $this->resetValidation();
    }

    /**
     * Edit data modal dengan backward compatibility
     */
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

            $capital = Capital::where('user_id', Auth::id())->findOrFail($id);

            $this->editId = $id;
            $this->isEdit = true;
            $this->tanggal = Carbon::parse($capital->tanggal)->format('Y-m-d');
            $this->nominal = $capital->nominal;

            // Set nilai untuk kolom opsional jika ada
            if ($this->hasColumn('keperluan')) {
                $this->keperluan = $capital->keperluan;
            } else {
                $this->keperluan = null;
            }
            
            if ($this->hasColumn('keterangan')) {
                $this->keterangan = $capital->keterangan;
            } else {
                $this->keterangan = null;
            }

            if ($this->hasColumn('jenis')) {
                $this->jenis = $capital->jenis ?? 'masuk';
            } else {
                $this->jenis = 'masuk';
            }

            $this->showModal = true;

            Log::info('Capital edited', [
                'capital_id' => $id,
                'user_id' => Auth::id(),
                'has_jenis_column' => $this->hasColumn('jenis')
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            session()->flash('error', 'Data tidak ditemukan atau Anda tidak memiliki akses.');
        } catch (\Exception $e) {
            Log::error('Error editing capital: ' . $e->getMessage(), [
                'capital_id' => $id,
                'user_id' => Auth::check() ? Auth::id() : null
            ]);
            session()->flash('error', 'Terjadi kesalahan saat memuat data untuk diedit.');
        }
    }

    /**
     * Simpan data modal (create/update) dengan backward compatibility
     */
    public function save()
    {
        try {
            // Cek autentikasi
            if (!Auth::check()) {
                session()->flash('error', 'Anda harus login terlebih dahulu.');
                return;
            }

            // Validasi data
            $this->validate($this->getRules(), $this->messages);

            // Persiapkan data dasar (kolom wajib)
            $data = [
                'user_id' => Auth::id(),
                'tanggal' => $this->tanggal,
                'nominal' => (float) $this->nominal,
            ];

            // Tambahkan kolom opsional jika ada dan tidak kosong
            if ($this->hasColumn('keperluan') && $this->keperluan) {
                $data['keperluan'] = trim($this->keperluan);
            }

            if ($this->hasColumn('keterangan') && $this->keterangan) {
                $data['keterangan'] = trim($this->keterangan);
            }

            if ($this->hasColumn('jenis')) {
                $data['jenis'] = $this->jenis;
            }

            

            // Proses simpan
            if ($this->isEdit && $this->editId) {
                // Update existing record
                $capital = Capital::where('user_id', Auth::id())->findOrFail($this->editId);
                $capital->update($data);
                session()->flash('message', 'Data modal berhasil diperbarui.');

                Log::info('Capital updated', [
                    'capital_id' => $this->editId,
                    'user_id' => Auth::id(),
                    'has_jenis_column' => $this->hasColumn('jenis')
                ]);
            } else {
                // Create new record
                Capital::create($data);
                session()->flash('message', 'Data modal berhasil ditambahkan.');

                Log::info('Capital created', [
                    'user_id' => Auth::id(),
                    'has_jenis_column' => $this->hasColumn('jenis')
                ]);
            }

            // Reset dan reload
            $this->closeModal();
            $this->loadCapitals();

        } catch (ValidationException $e) {
            // Biarkan validation exception di-handle otomatis
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error saving capital: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'is_edit' => $this->isEdit,
                'has_jenis_column' => $this->hasColumn('jenis'),
                'trace' => $e->getTraceAsString()
            ]);

            $errorMessage = 'Terjadi kesalahan saat menyimpan data.';
            
            if (str_contains($e->getMessage(), 'Unknown column') || 
                str_contains($e->getMessage(), 'Column not found')) {
                $errorMessage .= ' Struktur database tidak sesuai. Silakan jalankan migrasi.';
            }

            session()->flash('error', $errorMessage);
        }
    }

    /**
     * Hapus data modal dengan konfirmasi
     */
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

            $capital = Capital::where('user_id', Auth::id())->findOrFail($id);
            $capital->delete();

            $this->loadCapitals();
            session()->flash('message', 'Data modal berhasil dihapus.');

            Log::info('Capital deleted', [
                'capital_id' => $id,
                'user_id' => Auth::id()
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            session()->flash('error', 'Data tidak ditemukan atau Anda tidak memiliki akses.');
        } catch (\Exception $e) {
            Log::error('Error deleting capital: ' . $e->getMessage(), [
                'capital_id' => $id,
                'user_id' => Auth::check() ? Auth::id() : null
            ]);
            session()->flash('error', 'Gagal menghapus data. Silakan coba lagi.');
        }
    }

    /**
     * Konfirmasi delete (untuk compatibility)
     */
    public function confirmDelete($id)
    {
        $this->delete($id);
    }

    

    

    

    /**
     * Cek apakah kolom jenis ada (untuk backward compatibility)
     */
    public function hasJenisColumn()
    {
        return $this->hasColumn('jenis');
    }

    /**
     * Update listeners dengan backward compatibility check
     */
    

    public function updatedFilterJenis()
    {
        // Only reload if jenis column exists
        if ($this->hasColumn('jenis')) {
            $this->resetInput();
            $this->loadCapitals();
        }
    }

    public function updatedNominal()
    {
        $this->validateOnly('nominal');
    }

    public function updatedTanggal()
    {
        $this->validateOnly('tanggal');
    }

    /**
     * Helper methods
     */
    

    public function getCapitalsCollection()
    {
        return collect($this->capitals);
    }

    /**
     * Method untuk refresh struktur tabel (utility)
     */
    public function refreshTableStructure()
    {
        try {
            // Clear cache
            $this->columnCache = [];
            Capital::clearColumnCache();
            
            // Reload data
            $this->loadCapitals();
            
            session()->flash('message', 'Struktur tabel berhasil disegarkan.');
            
        } catch (\Exception $e) {
            Log::error('Error refreshing table structure: ' . $e->getMessage());
            session()->flash('error', 'Gagal menyegarkan struktur tabel.');
        }
    }

    /**
     * Method untuk mendapatkan informasi struktur tabel
     */
    public function getTableStructureInfo()
    {
        return [
            'has_keperluan' => $this->hasColumn('keperluan'),
            'has_keterangan' => $this->hasColumn('keterangan'),
            'has_jenis' => $this->hasColumn('jenis'),
            'total_capitals' => count($this->capitals),
            
        ];
    }

    /**
     * Render komponen dengan data yang sudah disiapkan
     */
    public function render()
    {
        $hasJenisColumn = $this->hasJenisColumn();
        $capitalsCollection = $this->getCapitalsCollection();

        if ($hasJenisColumn) {
            $modalMasuk = $capitalsCollection->where('jenis', 'masuk');
            $modalKeluar = $capitalsCollection->where('jenis', 'keluar');
        } else {
            $modalMasuk = $capitalsCollection;
            $modalKeluar = collect([]);
        }
        
        return view('livewire.capitals.modal-page', [
            'capitals' => $capitalsCollection,
            'modalMasuk' => $modalMasuk,
            'modalKeluar' => $modalKeluar,
            'totalMasuk' => $this->totalMasuk,
            'totalKeluar' => $this->totalKeluar,
            'saldo' => $this->saldo,
            'hasJenisColumn' => $hasJenisColumn,
            'tableStructure' => $this->getTableStructureInfo(),
            'monthNames' => $this->monthNames,
            'monthName' => $this->monthName,
            'filterYear' => $this->filterYear
        ]);
    }
}