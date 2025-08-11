<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Bep extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nama_produk',
        'modal_tetap',
        'harga_per_barang',
        'modal_per_barang',
    ];

    // Cast untuk handling decimal
    protected $casts = [
        'modal_tetap' => 'decimal:2',
        'harga_per_barang' => 'decimal:2',
        'modal_per_barang' => 'decimal:2',
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

    // Accessor untuk menghitung BEP otomatis
    public function getBepAttribute()
    {
        $keuntunganPerUnit = $this->harga_per_barang - $this->modal_per_barang;

        if ($keuntunganPerUnit <= 0) {
            return 0; // Tidak bisa BEP jika tidak ada keuntungan
        }

        return ceil($this->modal_tetap / $keuntunganPerUnit);
    }

    // Accessor untuk keuntungan per unit
    public function getKeuntunganPerUnitAttribute()
    {
        return $this->harga_per_barang - $this->modal_per_barang;
    }
}
