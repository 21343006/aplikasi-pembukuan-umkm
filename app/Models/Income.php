<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Tanggal diperlakukan sebagai objek Carbon
    protected $casts = [
        'tanggal' => 'date',
        'harga' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function getTotalAttribute()
    {
        return $this->jumlah_terjual * $this->harga_satuan;
    }
}
