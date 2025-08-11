<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FixedCost extends Model
{
    use HasFactory;

    protected $table = 'fixed_costs';
    
    protected $fillable = [
        'user_id',
        'tanggal',
        'keperluan',
        'nominal'
    ];

    // Cast untuk handling decimal dan tanggal
    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'decimal:2',
    ];

    // Relasi dengan User
    public function user()
    {
        return $this->belongsTo(User::class);
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

    // Scope untuk mendapatkan data berdasarkan bulan dan tahun (sudah include user filter via global scope)
    public function scopeByMonth($query, $month, $year)
    {
        return $query->whereMonth('tanggal', $month)
                     ->whereYear('tanggal', $year);
    }

    // Scope untuk mendapatkan total per bulan
    public function scopeMonthlyTotal($query, $month, $year)
    {
        return $query->byMonth($month, $year)->sum('nominal');
    }

    // Scope untuk mendapatkan data berdasarkan range bulan
    public function scopeByMonthRange($query, $startMonth, $endMonth, $year)
    {
        return $query->whereYear('tanggal', $year)
                     ->whereBetween(DB::raw('MONTH(tanggal)'), [$startMonth, $endMonth]);
    }

    // Method untuk mendapatkan total modal tetap per bulan (dengan user filter)
    public static function getTotalByMonth($month, $year)
    {
        return static::byMonth($month, $year)->sum('nominal');
    }

    // Method untuk mendapatkan semua keperluan unik (dengan user filter)
    public static function getUniqueKeperluan()
    {
        return static::select('keperluan')->distinct()->pluck('keperluan')->sort();
    }

    // Method untuk cek apakah keperluan sudah ada di bulan tertentu (dengan user filter)
    public static function isKeperluanExistsInMonth($keperluan, $month, $year)
    {
        return static::byMonth($month, $year)
                     ->where('keperluan', $keperluan)
                     ->exists();
    }
}
