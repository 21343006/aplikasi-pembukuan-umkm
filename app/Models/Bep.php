<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bep extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Cast untuk handling decimal
    protected $casts = [
        'modal_tetap' => 'decimal:2',
        'harga_per_barang' => 'decimal:2',
        'modal_per_barang' => 'decimal:2',
    ];

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