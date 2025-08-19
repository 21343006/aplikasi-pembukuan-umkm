<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class Capital extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'capitals';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'nama',
        'tanggal',
        'keperluan',
        'keterangan',
        'nominal',
        'jenis',
        'jumlah'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be guarded.
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [];

    /**
     * Default values for attributes.
     */
    protected $attributes = [
        'jenis' => 'masuk',
    ];

    /**
     * Cache untuk pengecekan kolom
     */
    private static $columnCache = [];

    /**
     * Cek apakah kolom ada di tabel dengan caching
     */
    public static function hasColumn($columnName)
    {
        if (!isset(self::$columnCache[$columnName])) {
            try {
                self::$columnCache[$columnName] = Schema::hasColumn('capitals', $columnName);
            } catch (\Exception $e) {
                Log::warning("Error checking column {$columnName}: " . $e->getMessage());
                self::$columnCache[$columnName] = false;
            }
        }
        return self::$columnCache[$columnName];
    }

    /**
     * Relasi dengan User
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Scope untuk filter berdasarkan user yang login
     */
    public function scopeForUser(Builder $query, $userId = null): Builder
    {
        $userId = $userId ?: Auth::id();
        return $query->where('user_id', $userId);
    }

    /**
     * Scope untuk filter berdasarkan bulan dan tahun
     */
    public function scopeByMonth(Builder $query, int $month, int $year): Builder
    {
        return $query->whereMonth('tanggal', $month)
                    ->whereYear('tanggal', $year);
    }

    /**
     * Scope untuk filter berdasarkan periode
     */
    public function scopePeriode(Builder $query, int $month, int $year): Builder
    {
        return $query->whereMonth('tanggal', $month)
                    ->whereYear('tanggal', $year);
    }

    /**
     * Scope untuk filter berdasarkan jenis transaksi (dengan backward compatibility)
     */
    public function scopeByJenis(Builder $query, string $jenis): Builder
    {
        if (self::hasColumn('jenis')) {
            return $query->where('jenis', $jenis);
        }
        return $query; // Skip filter jika kolom jenis belum ada
    }

    /**
     * Scope untuk transaksi masuk (dengan backward compatibility)
     */
    public function scopeMasuk(Builder $query): Builder
    {
        if (self::hasColumn('jenis')) {
            return $query->where('jenis', 'masuk');
        }
        return $query; // Anggap semua sebagai masuk jika kolom jenis belum ada
    }

    /**
     * Scope untuk transaksi keluar (dengan backward compatibility)
     */
    public function scopeKeluar(Builder $query): Builder
    {
        if (self::hasColumn('jenis')) {
            return $query->where('jenis', 'keluar');
        }
        return $query->whereRaw('1 = 0'); // Return empty jika kolom jenis belum ada
    }

    /**
     * Scope untuk filter berdasarkan tanggal range
     */
    public function scopeDateRange(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('tanggal', [$startDate, $endDate]);
    }

    /**
     * Scope untuk tahun tertentu
     */
    public function scopeByYear(Builder $query, int $year): Builder
    {
        return $query->whereYear('tanggal', $year);
    }

    /**
     * Scope untuk bulan tertentu
     */
    public function scopeByMonthOnly(Builder $query, int $month): Builder
    {
        return $query->whereMonth('tanggal', $month);
    }

    /**
     * Scope untuk hari ini
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('tanggal', now()->toDateString());
    }

    /**
     * Scope untuk minggu ini
     */
    public function scopeThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('tanggal', [
            now()->startOfWeek()->toDateString(),
            now()->endOfWeek()->toDateString()
        ]);
    }

    /**
     * Scope untuk bulan ini
     */
    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereMonth('tanggal', now()->month)
                    ->whereYear('tanggal', now()->year);
    }

    /**
     * Scope untuk tahun ini
     */
    public function scopeThisYear(Builder $query): Builder
    {
        return $query->whereYear('tanggal', now()->year);
    }

    /**
     * Accessor untuk format nominal dalam Rupiah
     */
    public function getFormattedNominalAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->nominal, 0, ',', '.');
    }

    /**
     * Accessor untuk format nominal singkat (K, M, B)
     */
    public function getFormattedNominalShortAttribute(): string
    {
        $nominal = $this->nominal;
        
        if ($nominal >= 1000000000) {
            return 'Rp ' . number_format($nominal / 1000000000, 1) . 'M';
        } elseif ($nominal >= 1000000) {
            return 'Rp ' . number_format($nominal / 1000000, 1) . 'Jt';
        } elseif ($nominal >= 1000) {
            return 'Rp ' . number_format($nominal / 1000, 1) . 'K';
        }
        
        return 'Rp ' . number_format((float) $nominal, 0, ',', '.');
    }

    /**
     * Accessor untuk format tanggal Indonesia
     */
    public function getFormattedTanggalAttribute(): string
    {
        return Carbon::parse($this->tanggal)->format('d/m/Y');
    }

    /**
     * Accessor untuk format tanggal lengkap
     */
    public function getFormattedTanggalLengkapAttribute(): string
    {
        $bulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        $date = Carbon::parse($this->tanggal);
        return $date->day . ' ' . $bulan[$date->month] . ' ' . $date->year;
    }

    /**
     * Accessor untuk format tanggal relatif
     */
    public function getFormattedTanggalRelatifAttribute(): string
    {
        return Carbon::parse($this->tanggal)->diffForHumans();
    }

    /**
     * Accessor untuk badge warna berdasarkan jenis (dengan backward compatibility)
     */
    public function getBadgeColorAttribute(): string
    {
        if (!self::hasColumn('jenis')) {
            return 'success'; // Default untuk backward compatibility
        }
        
        return match($this->jenis ?? 'masuk') {
            'masuk' => 'success',
            'keluar' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Accessor untuk badge text berdasarkan jenis (dengan backward compatibility)
     */
    public function getBadgeTextAttribute(): string
    {
        if (!self::hasColumn('jenis')) {
            return 'Masuk'; // Default untuk backward compatibility
        }
        
        return match($this->jenis ?? 'masuk') {
            'masuk' => 'Masuk',
            'keluar' => 'Keluar',
            default => 'Unknown'
        };
    }

    /**
     * Accessor untuk icon berdasarkan jenis (dengan backward compatibility)
     */
    public function getIconAttribute(): string
    {
        if (!self::hasColumn('jenis')) {
            return '↗'; // Default untuk backward compatibility
        }
        
        return match($this->jenis ?? 'masuk') {
            'masuk' => '↗',
            'keluar' => '↙',
            default => '→'
        };
    }

    /**
     * Accessor untuk CSS class berdasarkan jenis (dengan backward compatibility)
     */
    public function getCssClassAttribute(): string
    {
        if (!self::hasColumn('jenis')) {
            return 'text-success'; // Default untuk backward compatibility
        }
        
        return match($this->jenis ?? 'masuk') {
            'masuk' => 'text-success',
            'keluar' => 'text-danger',
            default => 'text-secondary'
        };
    }

    /**
     * Mutator untuk nominal - pastikan selalu positif
     */
    public function setNominalAttribute($value): void
    {
        $this->attributes['nominal'] = abs((float) $value);
    }

    /**
     * Mutator untuk keperluan - trim whitespace dan capitalize (dengan pengecekan kolom)
     */
    public function setKeperluanAttribute($value): void
    {
        if (self::hasColumn('keperluan')) {
            $this->attributes['keperluan'] = $value ? ucfirst(trim($value)) : null;
        }
    }

    /**
     * Mutator untuk keterangan - trim whitespace (dengan pengecekan kolom)
     */
    public function setKeteranganAttribute($value): void
    {
        if (self::hasColumn('keterangan')) {
            $this->attributes['keterangan'] = $value ? trim($value) : null;
        }
    }

    /**
     * Mutator untuk jenis - pastikan lowercase (dengan pengecekan kolom)
     */
    public function setJenisAttribute($value): void
    {
        if (self::hasColumn('jenis')) {
            $this->attributes['jenis'] = strtolower($value);
        }
    }

    /**
     * Boot method untuk model events
     */
    protected static function booted(): void
    {
        // Auto set user_id saat creating jika belum ada
        static::creating(function (Capital $capital) {
            if (!$capital->user_id && Auth::check()) {
                $capital->user_id = Auth::id();
            }
            
            // Set default jenis jika belum ada dan kolom jenis tersedia
            if (self::hasColumn('jenis') && !$capital->jenis) {
                $capital->jenis = 'masuk';
            }
        });

        // Log activity saat data dibuat
        static::created(function (Capital $capital) {
            Log::info('Capital created', [
                'id' => $capital->id,
                'user_id' => $capital->user_id,
                'jenis' => $capital->jenis ?? 'masuk',
                'nominal' => $capital->nominal,
                'tanggal' => Carbon::parse($capital->tanggal)->format('Y-m-d')
            ]);
        });

        // Log activity saat data diupdate
        static::updated(function (Capital $capital) {
            Log::info('Capital updated', [
                'id' => $capital->id,
                'user_id' => $capital->user_id,
                'changes' => $capital->getChanges()
            ]);
        });

        // Log activity saat data dihapus
        static::deleted(function (Capital $capital) {
            Log::info('Capital deleted', [
                'id' => $capital->id,
                'user_id' => $capital->user_id,
                'nominal' => $capital->nominal,
                'jenis' => $capital->jenis ?? 'masuk'
            ]);
        });
    }

    /**
     * Method untuk mendapatkan total masuk per user (dengan backward compatibility)
     */
    public static function getTotalMasuk($userId = null, $month = null, $year = null): float
    {
        $query = self::forUser($userId);
        
        if (self::hasColumn('jenis')) {
            $query->masuk();
        }
        
        if ($month && $year) {
            $query->byMonth($month, $year);
        }
        
        return $query->sum('nominal') ?? 0;
    }

    /**
     * Method untuk mendapatkan total keluar per user (dengan backward compatibility)
     */
    public static function getTotalKeluar($userId = null, $month = null, $year = null): float
    {
        if (!self::hasColumn('jenis')) {
            return 0; // Return 0 jika kolom jenis belum ada
        }
        
        $query = self::forUser($userId)->keluar();
        
        if ($month && $year) {
            $query->byMonth($month, $year);
        }
        
        return $query->sum('nominal') ?? 0;
    }

    /**
     * Method untuk mendapatkan saldo (total masuk - total keluar)
     */
    public static function getSaldo($userId = null, $month = null, $year = null): float
    {
        $totalMasuk = self::getTotalMasuk($userId, $month, $year);
        $totalKeluar = self::getTotalKeluar($userId, $month, $year);
        
        return $totalMasuk - $totalKeluar;
    }

    /**
     * Method untuk mendapatkan statistik lengkap (dengan backward compatibility)
     */
    public static function getStatistik($userId = null, $month = null, $year = null): array
    {
        $query = self::forUser($userId);
        
        if ($month && $year) {
            $query->byMonth($month, $year);
        }
        
        $hasJenisColumn = self::hasColumn('jenis');
        
        $totalMasuk = $hasJenisColumn ? $query->clone()->masuk()->sum('nominal') ?? 0 : $query->clone()->sum('nominal') ?? 0;
        $totalKeluar = $hasJenisColumn ? $query->clone()->keluar()->sum('nominal') ?? 0 : 0;
        $totalTransaksi = $query->count();
        $rataRata = $query->avg('nominal') ?? 0;
        $transaksiTerbesar = $query->clone()->max('nominal') ?? 0;
        $transaksiTerkecil = $query->clone()->min('nominal') ?? 0;
        
        $masukCount = $hasJenisColumn ? $query->clone()->masuk()->count() : $totalTransaksi;
        $keluarCount = $hasJenisColumn ? $query->clone()->keluar()->count() : 0;
        
        return [
            'total_masuk' => $totalMasuk,
            'total_keluar' => $totalKeluar,
            'saldo' => $totalMasuk - $totalKeluar,
            'total_transaksi' => $totalTransaksi,
            'rata_rata' => $rataRata,
            'transaksi_terbesar' => $transaksiTerbesar,
            'transaksi_terkecil' => $transaksiTerkecil,
            'persentase_masuk' => $totalTransaksi > 0 ? round(($masukCount / $totalTransaksi) * 100, 2) : 0,
            'persentase_keluar' => $totalTransaksi > 0 ? round(($keluarCount / $totalTransaksi) * 100, 2) : 0,
        ];
    }

    /**
     * Method untuk mendapatkan data chart bulanan (dengan backward compatibility)
     */
    public static function getChartData($userId = null, $year = null): array
    {
        $year = $year ?? now()->year;
        $data = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $masuk = self::getTotalMasuk($userId, $month, $year);
            $keluar = self::getTotalKeluar($userId, $month, $year);
            
            $data[] = [
                'month' => $month,
                'month_name' => Carbon::create()->month($month)->format('M'),
                'masuk' => $masuk,
                'keluar' => $keluar,
                'saldo' => $masuk - $keluar
            ];
        }
        
        return $data;
    }

    /**
     * Method untuk mendapatkan top transaksi (dengan backward compatibility)
     */
    public static function getTopTransaksi($userId = null, $limit = 5, $jenis = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = self::forUser($userId)->orderBy('nominal', 'desc');
        
        if ($jenis && self::hasColumn('jenis')) {
            $query->byJenis($jenis);
        }
        
        return $query->limit($limit)->get();
    }

    /**
     * Method untuk mendapatkan transaksi terbaru
     */
    public static function getRecentTransaksi($userId = null, $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return self::forUser($userId)
                  ->orderBy('tanggal', 'desc')
                  ->orderBy('created_at', 'desc')
                  ->limit($limit)
                  ->get();
    }

    /**
     * Method untuk mendapatkan summary harian (dengan backward compatibility)
     */
    public static function getDailySummary($userId = null, $date = null): array
    {
        $date = $date ? Carbon::parse($date) : now();
        
        $query = self::forUser($userId)->whereDate('tanggal', $date->toDateString());
        
        $hasJenisColumn = self::hasColumn('jenis');
        
        $masuk = $hasJenisColumn ? $query->clone()->masuk()->sum('nominal') ?? 0 : $query->clone()->sum('nominal') ?? 0;
        $keluar = $hasJenisColumn ? $query->clone()->keluar()->sum('nominal') ?? 0 : 0;
        $transaksi = $query->count();
        
        return [
            'tanggal' => $date->format('Y-m-d'),
            'tanggal_formatted' => $date->format('d/m/Y'),
            'total_masuk' => $masuk,
            'total_keluar' => $keluar,
            'saldo' => $masuk - $keluar,
            'total_transaksi' => $transaksi
        ];
    }

    /**
     * Method untuk validasi data sebelum save
     */
    public function validate(): bool
    {
        // Validasi user_id
        if (!$this->user_id) {
            return false;
        }

        // Validasi tanggal
        if (!$this->tanggal) {
            return false;
        }

        // Validasi tanggal tidak lebih dari hari ini
        if (Carbon::parse($this->tanggal)->isAfter(now())) {
            return false;
        }

        // Validasi nominal
        if (!$this->nominal || $this->nominal <= 0) {
            return false;
        }

        // Validasi jenis (jika kolom ada)
        if (self::hasColumn('jenis') && !in_array($this->jenis ?? 'masuk', ['masuk', 'keluar'])) {
            return false;
        }

        return true;
    }

    /**
     * Method untuk cek apakah transaksi dapat diedit
     */
    public function canEdit(): bool
    {
        // Cek ownership
        if ($this->user_id !== Auth::id()) {
            return false;
        }

        // Cek apakah transaksi tidak terlalu lama (opsional)
        $maxEditDays = config('app.capital_edit_days', 30);
        if ($this->created_at->diffInDays(now()) > $maxEditDays) {
            return false;
        }

        return true;
    }

    /**
     * Method untuk cek apakah transaksi dapat dihapus
     */
    public function canDelete(): bool
    {
        // Cek ownership
        if ($this->user_id !== Auth::id()) {
            return false;
        }

        // Cek apakah transaksi tidak terlalu lama (opsional)
        $maxDeleteDays = config('app.capital_delete_days', 7);
        if ($this->created_at->diffInDays(now()) > $maxDeleteDays) {
            return false;
        }

        return true;
    }

    /**
     * Convert ke array untuk export (dengan backward compatibility)
     */
    public function toExportArray(): array
    {
        $data = [
            'Tanggal' => $this->formatted_tanggal,
            'Nominal' => $this->formatted_nominal,
            'Dibuat' => $this->created_at->format('d/m/Y H:i')
        ];

        // Tambahkan kolom opsional jika tersedia
        if (self::hasColumn('jenis')) {
            $data['Jenis'] = ucfirst($this->jenis ?? 'masuk');
        }

        if (self::hasColumn('keperluan') && $this->keperluan) {
            $data['Keperluan'] = $this->keperluan;
        }

        if (self::hasColumn('keterangan') && $this->keterangan) {
            $data['Keterangan'] = $this->keterangan;
        }

        return $data;
    }

    /**
     * Method untuk clear column cache (utility)
     */
    public static function clearColumnCache(): void
    {
        self::$columnCache = [];
    }

    /**
     * Method untuk refresh column cache (utility)
     */
    public static function refreshColumnCache(): void
    {
        self::clearColumnCache();
        
        // Pre-load common columns
        $columns = ['keperluan', 'keterangan', 'jenis'];
        foreach ($columns as $column) {
            self::hasColumn($column);
        }
    }

    /**
     * Override getAttribute untuk backward compatibility
     */
    public function getAttribute($key)
    {
        // Handle missing columns gracefully
        if (!self::hasColumn($key)) {
            switch ($key) {
                case 'keperluan':
                case 'keterangan':
                    return null;
                case 'jenis':
                    return 'masuk';
                default:
                    return parent::getAttribute($key);
            }
        }

        return parent::getAttribute($key);
    }

    /**
     * Override setAttribute untuk backward compatibility
     */
    public function setAttribute($key, $value)
    {
        // Skip setting if column doesn't exist
        if (!self::hasColumn($key) && in_array($key, ['keperluan', 'keterangan', 'jenis'])) {
            return $this;
        }

        return parent::setAttribute($key, $value);
    }
}