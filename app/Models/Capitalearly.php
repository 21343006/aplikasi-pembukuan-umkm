<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Capitalearly extends Model
{
    use HasFactory;

    protected $table = 'capitalearlys';

    protected $fillable = [
        'user_id',
        'modal_awal'
    ];

    protected $casts = [
        'modal_awal' => 'decimal:2',
        'created_at' => 'datetime',
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
