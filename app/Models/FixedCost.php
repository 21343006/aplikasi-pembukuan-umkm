<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class FixedCost extends Model
{
    use HasFactory;

    protected $table = 'fixed_costs'; // Pastikan nama tabel sesuai dengan migrasi
    protected $fillable = ['tanggal', 'keperluan', 'nominal'];

    // Cast untuk handling decimal dan tanggal
    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'decimal:2',
    ];

    // Scope untuk mendapatkan data berdasarkan bulan dan tahun
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
                    ->whereBetween(\DB::raw('MONTH(tanggal)'), [$startMonth, $endMonth]);
    }

    // Method untuk mendapatkan total modal tetap per bulan
    public static function getTotalByMonth($month, $year)
    {
        return static::byMonth($month, $year)->sum('nominal');
    }

    // Method untuk mendapatkan semua keperluan unik
    public static function getUniqueKeperluan()
    {
        return static::select('keperluan')->distinct()->pluck('keperluan')->sort();
    }

    // Method untuk cek apakah keperluan sudah ada di bulan tertentu
    public static function isKeperluanExistsInMonth($keperluan, $month, $year)
    {
        return static::byMonth($month, $year)
                    ->where('keperluan', $keperluan)
                    ->exists();
    }
}