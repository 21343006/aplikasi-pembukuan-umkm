<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Reportharian extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'tanggal',
        'keterangan',
        'uang_masuk',
        'uang_keluar',
        'saldo',
    ];
    
    protected $table = 'reportharian'; 
    
    protected $casts = [
        'tanggal' => 'date',
        'uang_masuk' => 'decimal:2',
        'uang_keluar' => 'decimal:2',
        'saldo' => 'decimal:2',
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
}