<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Income extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
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
}