<?php

namespace App\Livewire\Reports;

use Livewire\Component;

use App\Models\Capitalearly;
use App\Models\Capital;
use App\Models\FixedCost;
use App\Models\Income;
use App\Models\Expenditure;
use App\Models\Debt;
use App\Models\Receivable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class ReportbulananList extends Component
{
    public $bulan;
    public $tahun;
    public $rekapHarian = [];
    
    // Saldo calculations
    public $saldoAwal = 0;
    public $saldoAkhirBulanIni = 0;
    
    // Main transaction totals
    public $totalUangMasuk = 0;
    public $totalUangKeluar = 0;
    
    // Modal calculations
    public $totalModalAwal = 0;
    public $totalModalMasuk = 0;
    public $totalModalKeluar = 0;
    public $totalModalTetap = 0;
    
    // Combined totals
    public $totalPemasukanBulanIni = 0;
    public $totalPengeluaranBulanIni = 0;
    public $monthlySummaryKeterangan;
    
    // Utang & Piutang
    public $totalUtang = 0;
    public $totalPiutang = 0;
    public $totalUtangLunas = 0;
    public $totalPiutangLunas = 0;
    public $totalUtangDibayarBulanIni = 0;
    public $totalPiutangDiterimaBulanIni = 0;
    public $utangDetails = [];
    public $piutangDetails = [];
    public $utangLunasDetails = [];
    public $piutangLunasDetails = [];

    /**
     * Initialize component with current month and year
     */
    public function mount(): void
    {
        $this->bulan = now()->month;
        $this->tahun = now()->year;
        $this->loadRekap();
    }

    /**
     * Handle month change
     */
    public function updatedBulan(): void
    {
        $this->loadRekap();
    }

    /**
     * Handle year change
     */
    public function updatedTahun(): void
    {
        $this->loadRekap();
    }

    /**
     * Load monthly recap data with all calculations
     */
    public function loadRekap(): void
    {
        $this->resetAllTotals();

        $bulan = (int) $this->bulan;
        $tahun = (int) $this->tahun;

        if ($bulan < 1 || $bulan > 12 || $tahun < 2020) {
            return;
        }

        try {
            // Calculate cumulative balance from all data before this month
            $this->saldoAwal = $this->hitungSaldoSebelumBulan($bulan, $tahun);

            // Dynamically load daily income from Income model
            $incomeDaily = Income::whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', $bulan)
                ->selectRaw('tanggal, SUM(jumlah_terjual * harga_satuan) as total_masuk')
                ->groupBy('tanggal')
                ->get()
                ->keyBy(function($item) {
                    return Carbon::parse($item->tanggal)->format('Y-m-d');
                });

            // Dynamically load daily expenditure from Expenditure model
            $expenditureDaily = Expenditure::whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', $bulan)
                ->selectRaw('tanggal, SUM(jumlah) as total_keluar')
                ->groupBy('tanggal')
                ->get()
                ->keyBy(function($item) {
                    return Carbon::parse($item->tanggal)->format('Y-m-d');
                });

            // Load daily debt payments and receivable collections
            $debtPaymentsDaily = Debt::whereYear('paid_date', $tahun)
                ->whereMonth('paid_date', $bulan)
                ->whereNotNull('paid_date')
                ->selectRaw('paid_date as tanggal, SUM(paid_amount) as total_debt_paid')
                ->groupBy('paid_date')
                ->get()
                ->keyBy(function($item) {
                    return Carbon::parse($item->tanggal)->format('Y-m-d');
                });

            $receivableCollectionsDaily = Receivable::whereYear('paid_date', $tahun)
                ->whereMonth('paid_date', $bulan)
                ->whereNotNull('paid_date')
                ->selectRaw('paid_date as tanggal, SUM(paid_amount) as total_receivable_collected')
                ->groupBy('paid_date')
                ->get()
                ->keyBy(function($item) {
                    return Carbon::parse($item->tanggal)->format('Y-m-d');
                });

            // Create a collection for daily income/expenditure with debt and receivable data
            $dailyTransactionsCollection = collect();
            $daysInMonth = Carbon::create($tahun, $bulan, 1)->daysInMonth;

            for ($i = 1; $i <= $daysInMonth; $i++) {
                $currentDate = Carbon::create($tahun, $bulan, $i)->format('Y-m-d');
                $masuk = $incomeDaily->get($currentDate)->total_masuk ?? 0;
                $keluar = $expenditureDaily->get($currentDate)->total_keluar ?? 0;
                $utangDibayar = $debtPaymentsDaily->get($currentDate)->total_debt_paid ?? 0;
                $piutangDiterima = $receivableCollectionsDaily->get($currentDate)->total_receivable_collected ?? 0;

                $dailyTransactionsCollection->push((object)[
                    'tanggal' => $currentDate,
                    'masuk' => (float) $masuk,
                    'keluar' => (float) $keluar,
                    'utang_dibayar' => (float) $utangDibayar,
                    'piutang_diterima' => (float) $piutangDiterima,
                ]);
            }

            // Load modal awal data for this month
            $modalAwalData = Capitalearly::whereYear('tanggal_input', $tahun)
                ->whereMonth('tanggal_input', $bulan)
                ->orderBy('tanggal_input')
                ->get();

            $modalCollection = $modalAwalData->map(function ($item) {
                return (object)[
                    'tanggal' => Carbon::parse($item->tanggal_input)->format('Y-m-d'),
                    'masuk' => (float) $item->modal_awal,
                    'keluar' => 0,
                ];
            });

            // Load modal tetap (fixed costs) for this month - add to 1st day as expense
            $modalTetapData = FixedCost::whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', $bulan)
                ->sum('nominal') ?? 0;

            $this->totalModalTetap = $modalTetapData;

            // Create fixed cost entry for the 1st day of the month
            $firstDayOfMonth = Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
            $fixedCostCollection = collect();
            
            if ($modalTetapData > 0) {
                $fixedCostCollection->push((object)[
                    'tanggal' => $firstDayOfMonth,
                    'masuk' => 0,
                    'keluar' => (float) $modalTetapData,
                    'utang_dibayar' => 0,
                    'piutang_diterima' => 0,
                ]);
            }

            // Load modal masuk data for this month (if column exists)
            $modalMasukCollection = collect();
            $this->totalModalMasuk = 0;
            
            if ($this->hasColumn('capitals', 'jenis')) {
                $modalMasukData = Capital::where('jenis', 'masuk')
                    ->whereYear('tanggal', $tahun)
                    ->whereMonth('tanggal', $bulan)
                    ->orderBy('tanggal')
                    ->get();

                $this->totalModalMasuk = $modalMasukData->sum('nominal') ?? 0;

            $modalMasukCollection = $modalMasukData->map(function ($item) {
                return (object)[
                    'tanggal' => Carbon::parse($item->tanggal)->format('Y-m-d'),
                    'masuk' => (float) $item->nominal,
                    'keluar' => 0,
                    'utang_dibayar' => 0,
                    'piutang_diterima' => 0,
                ];
            });
            }

            // Load modal keluar data for this month (if column exists)
            $modalKeluarCollection = collect();
            $this->totalModalKeluar = 0;
            
            if ($this->hasColumn('capitals', 'jenis')) {
                $modalKeluarData = Capital::where('jenis', 'keluar')
                    ->whereYear('tanggal', $tahun)
                    ->whereMonth('tanggal', $bulan)
                    ->orderBy('tanggal')
                    ->get();

                $this->totalModalKeluar = $modalKeluarData->sum('nominal') ?? 0;

                $modalKeluarCollection = $modalKeluarData->map(function ($item) {
                    return (object)[
                        'tanggal' => Carbon::parse($item->tanggal)->format('Y-m-d'),
                        'masuk' => 0,
                        'keluar' => (float) $item->nominal,
                        'utang_dibayar' => 0,
                        'piutang_diterima' => 0,
                    ];
                });
            }

            // Merge all collections, now including dynamic daily transactions
            // HAPUS modalCollection untuk menghindari double counting modal awal
            $merged = $dailyTransactionsCollection
                ->concat($modalMasukCollection)
                ->concat($fixedCostCollection)
                ->concat($modalKeluarCollection);

            $grouped = $merged->groupBy('tanggal')->map(function ($items) {
                return [
                    'masuk' => $items->sum('masuk'),
                    'keluar' => $items->sum('keluar'),
                    'utang_dibayar' => $items->sum('utang_dibayar'),
                    'piutang_diterima' => $items->sum('piutang_diterima'),
                ];
            });

            $this->rekapHarian = $grouped->sortKeys()->toArray();

            // Calculate main transaction totals dynamically from Income and Expenditure
            $this->totalUangMasuk = Income::whereYear('tanggal', $tahun)
                                        ->whereMonth('tanggal', $bulan)
                                        ->selectRaw('SUM(jumlah_terjual * harga_satuan) as total_income')
                                        ->value('total_income') ?? 0;

            $this->totalUangKeluar = Expenditure::whereYear('tanggal', $tahun)
                                            ->whereMonth('tanggal', $bulan)
                                            ->sum('jumlah') ?? 0;

            // Calculate modal awal total for this month
            $this->totalModalAwal = $modalAwalData->sum('modal_awal');

            // Load utang dan piutang data
            $this->loadDebtsAndReceivables($bulan, $tahun);

            // Calculate total debt payments and receivable collections for this month
            $this->totalUtangDibayarBulanIni = $debtPaymentsDaily->sum('total_debt_paid');
            $this->totalPiutangDiterimaBulanIni = $receivableCollectionsDaily->sum('total_receivable_collected');

            // Calculate combined totals
            $this->totalPemasukanBulanIni = $this->totalUangMasuk + $this->totalModalAwal + $this->totalModalMasuk;
            $this->totalPengeluaranBulanIni = $this->totalUangKeluar + $this->totalModalKeluar + $this->totalModalTetap;

            // Calculate final balance for this month including debt and receivable transactions
            $this->saldoAkhirBulanIni = $this->totalPemasukanBulanIni - $this->totalPengeluaranBulanIni - $this->totalUtangDibayarBulanIni + $this->totalPiutangDiterimaBulanIni;
            
            // Total saldo kumulatif akan dihitung di view berdasarkan saldo awal + transaksi harian
            // Tidak perlu menghitung di sini untuk menghindari penghitungan ganda
            // $this->totalSaldoKumulatif = $this->saldoAwal + $this->saldoAkhirBulanIni;

            // Populate monthly summary keterangan
            $this->monthlySummaryKeterangan = "Ringkasan Bulanan - Pemasukan: " . $this->formatCurrency($this->totalPemasukanBulanIni) . ", Pengeluaran: " . $this->formatCurrency($this->totalPengeluaranBulanIni);

        } catch (\Exception $e) {
            Log::error('Gagal memuat rekap bulanan: ' . $e->getMessage(), [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->resetAllTotals();
        }
    }

    /**
     * Load utang dan piutang data untuk bulan yang dipilih
     */
    private function loadDebtsAndReceivables(int $bulan, int $tahun): void
    {
        try {
            // Tanggal akhir bulan untuk filter
            $endOfMonth = Carbon::create($tahun, $bulan)->endOfMonth();
            $startOfMonth = Carbon::create($tahun, $bulan)->startOfMonth();

            // Load utang yang belum dibayar (berdasarkan jatuh tempo atau dibuat dalam periode)
            $this->utangDetails = Debt::where(function($query) use ($startOfMonth, $endOfMonth) {
                    // Utang yang jatuh tempo dalam periode ini
                    $query->whereBetween('due_date', [$startOfMonth, $endOfMonth])
                          // Atau utang yang dibuat dalam periode ini
                          ->orWhereBetween('created_at', [$startOfMonth, $endOfMonth]);
                })
                ->where('status', '!=', 'paid')
                ->orderBy('due_date')
                ->get()
                ->map(function($debt) {
                    return [
                        'id' => $debt->id,
                        'creditor_name' => $debt->creditor_name,
                        'description' => $debt->description,
                        'amount' => $debt->amount,
                        'remaining_amount' => $debt->remaining_amount,
                        'due_date' => $debt->due_date,
                        'created_at' => $debt->created_at,
                        'status' => $debt->status,
                        'is_overdue' => $debt->is_overdue,
                        'days_overdue' => $debt->days_overdue ?? 0,
                    ];
                })
                ->toArray();

            $this->totalUtang = collect($this->utangDetails)->sum('remaining_amount');

            // Load utang yang sudah dibayar dalam periode ini
            $this->utangLunasDetails = Debt::where(function($query) use ($startOfMonth, $endOfMonth) {
                    // Utang yang dibayar dalam periode ini
                    $query->whereBetween('paid_date', [$startOfMonth, $endOfMonth])
                          // Atau utang yang dibuat dan langsung lunas dalam periode ini
                          ->orWhere(function($subQuery) use ($startOfMonth, $endOfMonth) {
                              $subQuery->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                                       ->where('status', 'paid');
                          });
                })
                ->where('status', 'paid')
                ->orderBy('paid_date')
                ->get()
                ->map(function($debt) {
                    return [
                        'id' => $debt->id,
                        'creditor_name' => $debt->creditor_name,
                        'description' => $debt->description,
                        'amount' => $debt->amount,
                        'paid_amount' => $debt->paid_amount,
                        'paid_date' => $debt->paid_date,
                        'due_date' => $debt->due_date,
                        'created_at' => $debt->created_at,
                        'status' => $debt->status,
                    ];
                })
                ->toArray();

            $this->totalUtangLunas = collect($this->utangLunasDetails)->sum('paid_amount');

            // Load piutang yang belum diterima (berdasarkan jatuh tempo atau dibuat dalam periode)
            $this->piutangDetails = Receivable::where(function($query) use ($startOfMonth, $endOfMonth) {
                    // Piutang yang jatuh tempo dalam periode ini
                    $query->whereBetween('due_date', [$startOfMonth, $endOfMonth])
                          // Atau piutang yang dibuat dalam periode ini
                          ->orWhereBetween('created_at', [$startOfMonth, $endOfMonth]);
                })
                ->where('status', '!=', 'paid')
                ->orderBy('due_date')
                ->get()
                ->map(function($receivable) {
                    return [
                        'id' => $receivable->id,
                        'debtor_name' => $receivable->debtor_name,
                        'description' => $receivable->description,
                        'amount' => $receivable->amount,
                        'remaining_amount' => $receivable->remaining_amount,
                        'due_date' => $receivable->due_date,
                        'created_at' => $receivable->created_at,
                        'status' => $receivable->status,
                        'is_overdue' => $receivable->is_overdue,
                        'days_overdue' => $receivable->days_overdue ?? 0,
                    ];
                })
                ->toArray();

            $this->totalPiutang = collect($this->piutangDetails)->sum('remaining_amount');

            // Load piutang yang sudah diterima dalam periode ini
            $this->piutangLunasDetails = Receivable::where(function($query) use ($startOfMonth, $endOfMonth) {
                    // Piutang yang diterima dalam periode ini
                    $query->whereBetween('paid_date', [$startOfMonth, $endOfMonth])
                          // Atau piutang yang dibuat dan langsung lunas dalam periode ini
                          ->orWhere(function($subQuery) use ($startOfMonth, $endOfMonth) {
                              $subQuery->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                                       ->where('status', 'paid');
                          });
                })
                ->where('status', 'paid')
                ->orderBy('paid_date')
                ->get()
                ->map(function($receivable) {
                    return [
                        'id' => $receivable->id,
                        'debtor_name' => $receivable->debtor_name,
                        'description' => $receivable->description,
                        'amount' => $receivable->amount,
                        'paid_amount' => $receivable->paid_amount,
                        'paid_date' => $receivable->paid_date,
                        'due_date' => $receivable->due_date,
                        'created_at' => $receivable->created_at,
                        'status' => $receivable->status,
                    ];
                })
                ->toArray();

            $this->totalPiutangLunas = collect($this->piutangLunasDetails)->sum('paid_amount');

        } catch (\Exception $e) {
            Log::error('Error loading debts and receivables: ' . $e->getMessage(), [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'trace' => $e->getTraceAsString()
            ]);
            $this->resetDebtReceivableData();
        }
    }

    /**
     * Reset debt and receivable data
     */
    private function resetDebtReceivableData(): void
    {
        $this->utangDetails = [];
        $this->piutangDetails = [];
        $this->utangLunasDetails = [];
        $this->piutangLunasDetails = [];
        $this->totalUtang = 0;
        $this->totalPiutang = 0;
        $this->totalUtangLunas = 0;
        $this->totalPiutangLunas = 0;
        $this->totalUtangDibayarBulanIni = 0;
        $this->totalPiutangDiterimaBulanIni = 0;
    }

    /**
     * Calculate total cumulative balance from all periods before selected month
     * Enhanced to include modal awal, modal keluar, and fixed costs
     */
    private function hitungSaldoSebelumBulan(int $bulan, int $tahun): float
    {
        try {
            $totalSaldo = 0;
            $targetDate = Carbon::create($tahun, $bulan, 1);

            // Calculate from all Income before this month
            $totalIncomeBefore = Income::where('tanggal', '<', $targetDate)
                                    ->selectRaw('SUM(jumlah_terjual * harga_satuan) as total_income')
                                    ->value('total_income') ?? 0;

            // Calculate from all Expenditure before this month
            $totalExpenditureBefore = Expenditure::where('tanggal', '<', $targetDate)
                                            ->sum('jumlah') ?? 0;

            $totalSaldo += $totalIncomeBefore - $totalExpenditureBefore;

            // Calculate from all initial capital before this month  
            $allModal = Capitalearly::where('tanggal_input', '<', $targetDate)->get();
            
            // Total from initial capital
            $totalSaldo += $allModal->sum('modal_awal');

            // Calculate from capital inflow before this month (if column exists)
            if ($this->hasColumn('capitals', 'jenis')) {
                $totalModalMasuk = Capital::where('jenis', 'masuk')
                    ->where('tanggal', '<', $targetDate)
                    ->sum('nominal') ?? 0;

                $totalSaldo += $totalModalMasuk;
            }

            // Calculate from capital outflow before this month (if column exists)
            if ($this->hasColumn('capitals', 'jenis')) {
                $totalModalKeluar = Capital::where('jenis', 'keluar')
                    ->where('tanggal', '<', $targetDate)
                    ->sum('nominal') ?? 0;

                $totalSaldo -= $totalModalKeluar;
            }

            // Calculate from fixed costs before this month
            $totalFixedCosts = FixedCost::where('tanggal', '<', $targetDate)
                ->sum('nominal') ?? 0;

            $totalSaldo -= $totalFixedCosts;

            // Calculate debt payments before this month (outflow - mengurangi saldo)
            $totalDebtPayments = Debt::where('paid_date', '<', $targetDate)
                ->whereNotNull('paid_date')
                ->sum('paid_amount') ?? 0;

            $totalSaldo -= $totalDebtPayments;

            // Calculate receivable collections before this month (inflow - menambah saldo)
            $totalReceivableCollections = Receivable::where('paid_date', '<', $targetDate)
                ->whereNotNull('paid_date')
                ->sum('paid_amount') ?? 0;

            $totalSaldo += $totalReceivableCollections;

            return $totalSaldo;

        } catch (\Exception $e) {
            Log::error('Error menghitung saldo sebelum bulan: ' . $e->getMessage(), [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            return 0;
        }
    }

    /**
     * Reset all totals to zero
     */
    private function resetAllTotals(): void
    {
        $this->rekapHarian = [];
        $this->saldoAwal = 0;
        $this->saldoAkhirBulanIni = 0;
        $this->totalUangMasuk = 0;
        $this->totalUangKeluar = 0;
        $this->totalModalAwal = 0;
        $this->totalModalMasuk = 0;
        $this->totalModalKeluar = 0;
        $this->totalModalTetap = 0;
        $this->totalPemasukanBulanIni = 0;
        $this->totalPengeluaranBulanIni = 0;
        $this->resetDebtReceivableData();
    }

    /**
     * Check if a column exists in a table
     */
    private function hasColumn(string $table, string $column): bool
    {
        try {
            return Schema::hasColumn($table, $column);
        } catch (\Exception $e) {
            Log::warning("Error checking column {$column} in table {$table}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get detailed breakdown of monthly data
     */
    public function getDetailedBreakdown(): array
    {
        return [
            'periode' => [
                'bulan' => $this->bulan,
                'tahun' => $this->tahun,
                'nama_bulan' => $this->getNamaBulan((int) $this->bulan)
            ],
            'saldo' => [
                'awal' => $this->saldoAwal,
                'perubahan_bulan_ini' => $this->saldoAkhirBulanIni,
                'kumulatif' => $this->saldoAwal + $this->saldoAkhirBulanIni
            ],
            'pemasukan' => [
                'transaksi_harian' => $this->totalUangMasuk,
                'modal_awal' => $this->totalModalAwal,
                'total' => $this->totalPemasukanBulanIni
            ],
            'pengeluaran' => [
                'transaksi_harian' => $this->totalUangKeluar,
                'modal_keluar' => $this->totalModalKeluar,
                'modal_tetap' => $this->totalModalTetap,
                'total' => $this->totalPengeluaranBulanIni
            ]
        ];
    }

    /**
     * Get month name in Indonesian
     */
    private function getNamaBulan(int $bulan): string
    {
        $namaButel = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return $namaButel[$bulan] ?? 'Unknown';
    }

    /**
     * Export laporan bulanan ke CSV
     */
    public function exportCSV()
    {
        try {
            if (!Auth::check()) {
                session()->flash('error', 'Anda harus login untuk mengexport laporan.');
                return;
            }

            $bulan = (int) $this->bulan;
            $tahun = (int) $this->tahun;
            
            if ($bulan < 1 || $bulan > 12 || $tahun < 2020) {
                session()->flash('error', 'Periode laporan tidak valid.');
                return;
            }

            $namaBulan = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];

            $fileName = 'Laporan_Keuangan_Bulanan_' . $namaBulan[$bulan] . '_' . $tahun . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0'
            ];

            $callback = function() use ($bulan, $tahun, $namaBulan) {
                $file = fopen('php://output', 'w');
                
                // Add BOM for UTF-8
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                
                // Header laporan dengan format yang rapi
                fputcsv($file, ['LAPORAN KEUANGAN BULANAN'], ',');
                fputcsv($file, ['Periode', $namaBulan[$bulan] . ' ' . $tahun], ',');
                fputcsv($file, ['Tanggal Export', now()->format('d/m/Y H:i:s')], ',');
                fputcsv($file, [''], ','); // Empty row

                // Ringkasan Keuangan dalam format tabel
                fputcsv($file, ['RINGKASAN KEUANGAN'], ',');
                fputcsv($file, ['Keterangan', 'Jumlah (Rp)'], ',');
                fputcsv($file, ['Saldo Awal Bulan', number_format($this->saldoAwal, 0, ',', '.')], ',');
                fputcsv($file, ['Total Pemasukan', number_format($this->totalPemasukanBulanIni, 0, ',', '.')], ',');
                fputcsv($file, ['Total Pengeluaran', number_format($this->totalPengeluaranBulanIni, 0, ',', '.')], ',');
                fputcsv($file, ['Utang Dibayar', number_format($this->totalUtangDibayarBulanIni, 0, ',', '.')], ',');
                fputcsv($file, ['Piutang Diterima', number_format($this->totalPiutangDiterimaBulanIni, 0, ',', '.')], ',');
                fputcsv($file, ['Saldo Akhir', number_format($this->saldoAwal + $this->saldoAkhirBulanIni, 0, ',', '.')], ',');
                fputcsv($file, [''], ','); // Empty row

                // Breakdown Pemasukan
                fputcsv($file, ['BREAKDOWN PEMASUKAN'], ',');
                fputcsv($file, ['Jenis Pemasukan', 'Jumlah (Rp)'], ',');
                fputcsv($file, ['Penjualan', number_format($this->totalUangMasuk, 0, ',', '.')], ',');
                fputcsv($file, ['Modal Awal', number_format($this->totalModalAwal, 0, ',', '.')], ',');
                fputcsv($file, ['Modal Tambahan', number_format($this->totalModalMasuk, 0, ',', '.')], ',');
                fputcsv($file, ['TOTAL PEMASUKAN', number_format($this->totalPemasukanBulanIni, 0, ',', '.')], ',');
                fputcsv($file, [''], ','); // Empty row

                // Breakdown Pengeluaran
                fputcsv($file, ['BREAKDOWN PENGELUARAN'], ',');
                fputcsv($file, ['Jenis Pengeluaran', 'Jumlah (Rp)'], ',');
                fputcsv($file, ['Operasional', number_format($this->totalUangKeluar, 0, ',', '.')], ',');
                fputcsv($file, ['Modal Keluar', number_format($this->totalModalKeluar, 0, ',', '.')], ',');
                fputcsv($file, ['Biaya Tetap', number_format($this->totalModalTetap, 0, ',', '.')], ',');
                fputcsv($file, ['TOTAL PENGELUARAN', number_format($this->totalPengeluaranBulanIni, 0, ',', '.')], ',');
                fputcsv($file, [''], ','); // Empty row

                // Status Utang & Piutang
                fputcsv($file, ['STATUS UTANG & PIUTANG'], ',');
                fputcsv($file, ['Keterangan', 'Jumlah (Rp)'], ',');
                fputcsv($file, ['Utang Belum Lunas', number_format($this->totalUtang, 0, ',', '.')], ',');
                fputcsv($file, ['Piutang Belum Tertagih', number_format($this->totalPiutang, 0, ',', '.')], ',');
                fputcsv($file, ['Utang Dibayar Periode Ini', number_format($this->totalUtangDibayarBulanIni, 0, ',', '.')], ',');
                fputcsv($file, ['Piutang Diterima Periode Ini', number_format($this->totalPiutangDiterimaBulanIni, 0, ',', '.')], ',');
                fputcsv($file, [''], ','); // Empty row

                // Rekap Harian
                fputcsv($file, ['REKAP TRANSAKSI HARIAN'], ',');
                fputcsv($file, [
                    'Tanggal', 
                    'Pemasukan (Rp)', 
                    'Pengeluaran (Rp)', 
                    'Utang Dibayar (Rp)', 
                    'Piutang Diterima (Rp)', 
                    'Saldo Harian (Rp)', 
                    'Saldo Kumulatif (Rp)'
                ], ',');

                $saldoKumulatif = $this->saldoAwal;
                
                foreach ($this->rekapHarian as $tanggal => $data) {
                    $pemasukan = (float) $data['masuk'];
                    $pengeluaran = (float) $data['keluar'];
                    $utangDibayar = (float) ($data['utang_dibayar'] ?? 0);
                    $piutangDiterima = (float) ($data['piutang_diterima'] ?? 0);
                    $saldoHarian = $pemasukan - $pengeluaran - $utangDibayar + $piutangDiterima;
                    $saldoKumulatif += $saldoHarian;

                    fputcsv($file, [
                        Carbon::parse($tanggal)->format('d/m/Y'),
                        number_format($pemasukan, 0, ',', '.'),
                        number_format($pengeluaran, 0, ',', '.'),
                        number_format($utangDibayar, 0, ',', '.'),
                        number_format($piutangDiterima, 0, ',', '.'),
                        number_format($saldoHarian, 0, ',', '.'),
                        number_format($saldoKumulatif, 0, ',', '.')
                    ], ',');
                }

                fputcsv($file, [''], ','); // Empty row

                // Detail Utang Belum Dibayar
                if (count($this->utangDetails) > 0) {
                    fputcsv($file, ['DETAIL UTANG BELUM LUNAS'], ',');
                    fputcsv($file, ['Kreditur', 'Sisa Utang (Rp)', 'Jatuh Tempo', 'Status'], ',');
                    
                    foreach ($this->utangDetails as $utang) {
                        fputcsv($file, [
                            $utang['creditor_name'],
                            number_format($utang['remaining_amount'] ?? $utang['amount'], 0, ',', '.'),
                            isset($utang['due_date']) ? Carbon::parse($utang['due_date'])->format('d/m/Y') : 'Tidak Ada',
                            (isset($utang['is_overdue']) && $utang['is_overdue']) ? 'TERLAMBAT' : 'Normal'
                        ], ',');
                    }
                    fputcsv($file, [''], ','); // Empty row
                }

                // Detail Piutang Belum Diterima
                if (count($this->piutangDetails) > 0) {
                    fputcsv($file, ['DETAIL PIUTANG BELUM TERTAGIH'], ',');
                    fputcsv($file, ['Debitur', 'Sisa Piutang (Rp)', 'Jatuh Tempo', 'Status'], ',');
                    
                    foreach ($this->piutangDetails as $piutang) {
                        fputcsv($file, [
                            $piutang['debtor_name'],
                            number_format($piutang['remaining_amount'] ?? $piutang['amount'], 0, ',', '.'),
                            isset($piutang['due_date']) ? Carbon::parse($piutang['due_date'])->format('d/m/Y') : 'Tidak Ada',
                            (isset($piutang['is_overdue']) && $piutang['is_overdue']) ? 'TERLAMBAT' : 'Normal'
                        ], ',');
                    }
                    fputcsv($file, [''], ','); // Empty row
                }
                
                // Footer dengan informasi tambahan
                fputcsv($file, ['INFORMASI LAPORAN'], ',');
                fputcsv($file, ['File ini dibuat otomatis oleh sistem'], ',');
                fputcsv($file, ['Semua angka dalam Rupiah (Rp)'], ',');
                fputcsv($file, ['Format angka: 1.000.000 = Satu Juta Rupiah'], ',');

                fclose($file);
            };

            session()->flash('message', 'Laporan berhasil diexport ke CSV.');
            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            Log::error('Error exporting monthly report: ' . $e->getMessage(), [
                'bulan' => $this->bulan,
                'tahun' => $this->tahun,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            session()->flash('error', 'Gagal mengexport laporan: ' . $e->getMessage());
            return;
        }
    }

    /**
     * Get available years for filter
     */
    public function getAvailableYears(): array
    {
        $currentYear = now()->year;
        return range($currentYear + 1, 2020);
    }

    /**
     * Get available months
     */
    public function getAvailableMonths(): array
    {
        return [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
    }

    /**
     * Format currency for display
     */
    public function formatCurrency(float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    /**
     * Get percentage of expenses vs income
     */
    public function getExpensePercentage(): float
    {
        if ($this->totalPemasukanBulanIni <= 0) {
            return 0;
        }

        return round(($this->totalPengeluaranBulanIni / $this->totalPemasukanBulanIni) * 100, 2);
    }

    /**
     * Check if current month has any data
     */
    public function hasDataForCurrentMonth(): bool
    {
        return $this->totalPemasukanBulanIni > 0 || $this->totalPengeluaranBulanIni > 0;
    }

    /**
     * Get monthly performance indicator
     */
    public function getMonthlyPerformance(): string
    {
        if ($this->saldoAkhirBulanIni > 0) {
            return 'surplus';
        } elseif ($this->saldoAkhirBulanIni < 0) {
            return 'deficit';
        } else {
            return 'balanced';
        }
    }

    /**
     * Get detailed modal breakdown for this month
     */
    public function getModalBreakdown(): array
    {
        $bulan = (int) $this->bulan;
        $tahun = (int) $this->tahun;

        try {
            // Get modal awal details
            $modalAwalDetails = Capitalearly::whereYear('tanggal_input', $tahun)
                ->whereMonth('tanggal_input', $bulan)
                ->select('tanggal_input', 'modal_awal')
                ->orderBy('tanggal_input')
                ->get()
                ->toArray();

            // Get modal keluar details (if column exists)
            $modalKeluarDetails = [];
            if ($this->hasColumn('capitals', 'jenis')) {
                $modalKeluarDetails = Capital::where('jenis', 'keluar')
                    ->whereYear('tanggal', $tahun)
                    ->whereMonth('tanggal', $bulan)
                    ->select('tanggal', 'nominal', 'keperluan', 'keterangan')
                    ->orderBy('tanggal')
                    ->get()
                    ->toArray();
            }

            // Get fixed cost details
            $modalTetapDetails = FixedCost::whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', $bulan)
                ->select('tanggal', 'keperluan', 'nominal')
                ->orderBy('keperluan')
                ->get()
                ->toArray();

            return [
                'modal_awal' => $modalAwalDetails,
                'modal_keluar' => $modalKeluarDetails,
                'modal_tetap' => $modalTetapDetails
            ];

        } catch (\Exception $e) {
            Log::error('Error getting modal breakdown: ' . $e->getMessage(), [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'user_id' => Auth::id()
            ]);
            return [
                'modal_awal' => [],
                'modal_keluar' => [],
                'modal_tetap' => []
            ];
        }
    }

    /**
     * Export monthly data to array for external use
     */
    public function exportMonthlyData(): array
    {
        return [
            'periode' => [
                'bulan' => $this->bulan,
                'tahun' => $this->tahun,
                'nama_bulan' => $this->getNamaBulan((int) $this->bulan)
            ],
            'saldo' => [
                'awal' => $this->saldoAwal,
                'akhir_bulan_ini' => $this->saldoAkhirBulanIni,
                'kumulatif' => $this->saldoAwal + $this->saldoAkhirBulanIni
            ],
            'transaksi_harian' => [
                'masuk' => $this->totalUangMasuk,
                'keluar' => $this->totalUangKeluar,
                'net' => $this->totalUangMasuk - $this->totalUangKeluar
            ],
            'modal' => [
                'awal' => $this->totalModalAwal,
                'keluar' => $this->totalModalKeluar,
                'tetap' => $this->totalModalTetap
            ],
            'total' => [
                'pemasukan' => $this->totalPemasukanBulanIni,
                'pengeluaran' => $this->totalPengeluaranBulanIni,
                'net' => $this->saldoAkhirBulanIni
            ],
            'rekap_harian' => $this->rekapHarian,
            'performance' => $this->getMonthlyPerformance(),
            'expense_percentage' => $this->getExpensePercentage()
        ];
    }

    /**
     * Get monthly summary data
     */
    public function getMonthlySummary(): array
    {
        return [
            'periode' => [
                'bulan' => $this->bulan,
                'tahun' => $this->tahun,
                'nama_bulan' => $this->getNamaBulan((int) $this->bulan)
            ],
            'saldo' => [
                'awal' => $this->saldoAwal,
                'perubahan_bulan_ini' => $this->saldoAkhirBulanIni,
                'kumulatif' => $this->saldoAwal + $this->saldoAkhirBulanIni
            ],
            'pemasukan' => [
                'transaksi_harian' => $this->totalUangMasuk,
                'modal_awal' => $this->totalModalAwal,
                'total' => $this->totalPemasukanBulanIni
            ],
            'pengeluaran' => [
                'transaksi_harian' => $this->totalUangKeluar,
                'modal_keluar' => $this->totalModalKeluar,
                'modal_tetap' => $this->totalModalTetap,
                'total' => $this->totalPengeluaranBulanIni
            ]
        ];
    }

    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.reports.reportbulanan-list', [
            'availableYears' => $this->getAvailableYears(),
            'availableMonths' => $this->getAvailableMonths(),
            'detailedBreakdown' => $this->getDetailedBreakdown(),
            'modalBreakdown' => $this->getModalBreakdown(),
            'monthlyPerformance' => $this->getMonthlyPerformance(),
            'expensePercentage' => $this->getExpensePercentage(),
            'hasData' => $this->hasDataForCurrentMonth()
        ]);
    }
}