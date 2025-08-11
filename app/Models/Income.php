<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
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
    ];

    // Tanggal diperlakukan sebagai objek Carbon
    protected $casts = [
        'tanggal' => 'date',
        'harga_satuan' => 'decimal:2',
    ];

    // Relasi dengan User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accessor untuk total pendapatan per produk
    public function getTotalAttribute()
    {
        return $this->jumlah_terjual * $this->harga_satuan;
    }
}