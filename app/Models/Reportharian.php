<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reportharian extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $table = 'reportharian'; 
    protected $casts = [
        'tanggal' => 'date',
    ];
}
