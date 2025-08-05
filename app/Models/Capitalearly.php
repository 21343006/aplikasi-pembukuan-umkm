<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Capitalearly extends Model
{
    use HasFactory;

    protected $table = 'capitalearlys'; // Nama tabel

    protected $fillable = ['modal_awal']; // Kolom yang boleh diisi

    protected $casts = [
        'modal_awal' => 'decimal:2', // Casting otomatis ke format desimal
        'created_at' => 'datetime', // Casting tanggal
    ];
}
