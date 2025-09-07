<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class Income extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'tanggal',
        'produk',
        'jumlah_terjual',
        'harga_satuan',
        'biaya_per_unit',
        'total_pendapatan',
        'laba',
    ];

    // Tanggal diperlakukan sebagai objek Carbon
    protected $casts = [
        'tanggal' => 'date',
        'harga_satuan' => 'decimal:2',
        'jumlah_terjual' => 'integer',
        'biaya_per_unit' => 'decimal:2',
        'total_pendapatan' => 'decimal:2',
        'laba' => 'decimal:2',
    ];

    // Relasi dengan User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relasi dengan Product
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Global scope untuk auto-filter berdasarkan user yang login
    protected static function booted(): void
    {
        static::addGlobalScope('user', function (Builder $query) {
            if (Auth::check()) {
                $query->where('user_id', Auth::id());
            }
        });
    }

    /**
     * Scope untuk filter berdasarkan user yang sedang login
     * PERBAIKAN: Tambahkan pengecekan dan gunakan Auth facade
     */
    public function scopeForCurrentUser($query)
    {
        // Opsi 1: Menggunakan Auth facade (lebih eksplisit)
        return $query->where('user_id', Auth::id());
        
        // Opsi 2: Dengan pengecekan null safety
        // $userId = Auth::id();
        // if ($userId) {
        //     return $query->where('user_id', $userId);
        // }
        // return $query; // atau throw exception jika user tidak login
    }

    /**
     * Scope untuk filter berdasarkan user yang sedang login (dengan null safety)
     */
    public function scopeForCurrentUserSafe($query)
    {
        $userId = Auth::id();
        
        if (!$userId) {
            // Jika tidak ada user yang login, kembalikan query kosong
            return $query->whereRaw('1 = 0');
            
            // Atau bisa throw exception:
            // throw new \Exception('User not authenticated');
        }
        
        return $query->where('user_id', $userId);
    }

    /**
     * Scope untuk filter berdasarkan user tertentu
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope untuk filter berdasarkan tahun
     */
    public function scopeByYear($query, $year)
    {
        return $query->whereYear('tanggal', $year);
    }

    /**
     * Scope untuk filter berdasarkan bulan dan tahun
     */
    public function scopeByMonth($query, $year, $month)
    {
        return $query->whereYear('tanggal', $year)
                     ->whereMonth('tanggal', $month);
    }

    /**
     * Alternative: Static method untuk mendapatkan income user saat ini
     */
    public static function forCurrentUser()
    {
        return static::where('user_id', Auth::id());
    }

    /**
     * Alternative: Static method dengan null safety
     */
    public static function forCurrentUserSafe()
    {
        $userId = Auth::id();
        
        if (!$userId) {
            return static::whereRaw('1 = 0'); // Return empty collection
        }
        
        return static::where('user_id', $userId);
    }

    /**
     * Accessor untuk total pendapatan (jika tidak diisi, hitung otomatis)
     */
    public function getTotalPendapatanAttribute($value)
    {
        if ($value == 0 || $value === null) {
            return $this->jumlah_terjual * $this->harga_satuan;
        }
        return $value;
    }

    /**
     * Accessor untuk laba (jika tidak diisi, hitung otomatis)
     */
    public function getLabaAttribute($value)
    {
        if ($value == 0 || $value === null) {
            $totalPendapatan = $this->total_pendapatan;
            $totalBiaya = $this->jumlah_terjual * $this->biaya_per_unit;
            return $totalPendapatan - $totalBiaya;
        }
        return $value;
    }

    /**
     * Mutator untuk menghitung total pendapatan otomatis
     */
    public function setTotalPendapatanAttribute($value)
    {
        if ($value == 0 || $value === null) {
            $this->attributes['total_pendapatan'] = $this->jumlah_terjual * $this->harga_satuan;
        } else {
            $this->attributes['total_pendapatan'] = $value;
        }
    }

    /**
     * Mutator untuk menghitung laba otomatis
     */
    public function setLabaAttribute($value)
    {
        if ($value == 0 || $value === null) {
            $totalPendapatan = $this->total_pendapatan ?: ($this->jumlah_terjual * $this->harga_satuan);
            $totalBiaya = $this->jumlah_terjual * $this->biaya_per_unit;
            $this->attributes['laba'] = $totalPendapatan - $totalBiaya;
        } else {
            $this->attributes['laba'] = $value;
        }
    }

    /**
     * Boot method untuk menghitung otomatis sebelum save
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($income) {
            // Auto set user_id saat creating jika belum ada
            if (!$income->user_id && Auth::check()) {
                $income->user_id = Auth::id();
            }
        });

        static::saving(function ($income) {
            // Hitung total pendapatan jika belum diisi
            if ($income->total_pendapatan == 0 || $income->total_pendapatan === null) {
                $income->total_pendapatan = $income->jumlah_terjual * $income->harga_satuan;
            }

            // Hitung laba jika belum diisi
            if ($income->laba == 0 || $income->laba === null) {
                $income->laba = $income->total_pendapatan - ($income->jumlah_terjual * $income->biaya_per_unit);
            }

            // Sync dengan product jika ada product_id
            if ($income->product_id) {
                $product = Product::find($income->product_id);
                if ($product) {
                    // Update product name jika berbeda
                    if ($product->name !== $income->produk) {
                        $income->produk = $product->name;
                    }
                }
            }
        });

        static::saved(function ($income) {
            // Update product quantity setelah income disimpan
            if ($income->product_id) {
                $product = Product::find($income->product_id);
                if ($product) {
                    $quantityBefore = $product->quantity;
                    $product->updateQuantityFromSales();
                    
                    // Catat riwayat stok keluar
                    $product->recordStockHistory(
                        'out',
                        -$income->jumlah_terjual, // negatif karena stok keluar
                        $quantityBefore,
                        $product->quantity,
                        "Penjualan: {$income->jumlah_terjual} unit",
                        'App\\Models\\Income',
                        $income->id
                    );
                }
            }
        });
    }

    /**
     * Get product name from product relation
     */
    public function getProductNameAttribute()
    {
        return $this->product ? $this->product->name : $this->produk;
    }

    /**
     * Scope untuk filter berdasarkan product
     */
    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope untuk filter berdasarkan nama produk
     */
    public function scopeByProductName($query, $productName)
    {
        return $query->where('produk', $productName);
    }

    /**
     * Get total sales for a specific product
     */
    public static function getTotalSalesByProduct($productId)
    {
        return static::where('product_id', $productId)->sum('jumlah_terjual');
    }

    /**
     * Get total revenue for a specific product
     */
    public static function getTotalRevenueByProduct($productId)
    {
        return static::where('product_id', $productId)->sum('total_pendapatan');
    }

    /**
     * Get total profit for a specific product
     */
    public static function getTotalProfitByProduct($productId)
    {
        return static::where('product_id', $productId)->sum('laba');
    }
}