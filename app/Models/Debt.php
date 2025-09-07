<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Debt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'creditor_name',
        'description',
        'amount',
        'due_date',
        'status',
        'paid_amount',
        'paid_date',
        'notes',
    ];

    protected $attributes = [
        'paid_amount' => null,
        'status' => 'unpaid',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_date' => 'date',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

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

    // Auto set user_id saat creating dan handle paid_amount
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($debt) {
            if (!$debt->user_id && Auth::check()) {
                $debt->user_id = Auth::id();
            }
            // Set paid_amount to null instead of 0 for new records
            if ($debt->paid_amount === 0) {
                $debt->paid_amount = null;
            }
        });
    }

    public function getRemainingAmountAttribute()
    {
        return $this->amount - ($this->paid_amount ?? 0);
    }

    public function getIsOverdueAttribute()
    {
        return $this->due_date < now() && $this->status !== 'paid';
    }

    public function getDaysOverdueAttribute()
    {
        if ($this->status === 'paid') {
            return 0;
        }
        return (int) max(0, now()->diffInDays($this->due_date, false));
    }

    /**
     * Get detailed status based on payment amount and due date
     */
    public function getDetailedStatusAttribute()
    {
        $now = Carbon::now();
        $isOverdue = $this->due_date < $now;
        $daysOverdue = $isOverdue ? (int) $now->diffInDays($this->due_date, false) : 0;
        
        // Jika sudah dibayar lunas
        if (($this->paid_amount ?? 0) >= $this->amount) {
            return 'Lunas';
        }
        
        // Jika belum dibayar sepeserpun
        if (!$this->paid_amount || $this->paid_amount == 0) {
            if ($isOverdue) {
                return "Belum Dibayar (Terlambat {$daysOverdue} hari)";
            }
            return 'Belum Dibayar';
        }
        
        // Jika sudah dibayar sebagian
        if ($this->paid_amount > 0 && $this->paid_amount < $this->amount) {
            if ($isOverdue) {
                return "Dibayar Sebagian (Terlambat {$daysOverdue} hari)";
            }
            return 'Dibayar Sebagian';
        }
        
        return 'Belum Dibayar';
    }

    /**
     * Get status badge class for styling
     */
    public function getStatusBadgeClassAttribute()
    {
        $now = Carbon::now();
        $isOverdue = $this->due_date < $now;
        
        // Jika sudah dibayar lunas
        if (($this->paid_amount ?? 0) >= $this->amount) {
            return 'bg-success';
        }
        
        // Jika belum dibayar sepeserpun
        if (!$this->paid_amount || $this->paid_amount == 0) {
            if ($isOverdue) {
                return 'bg-danger';
            }
            return 'bg-warning';
        }
        
        // Jika sudah dibayar sebagian
        if ($this->paid_amount > 0 && $this->paid_amount < $this->amount) {
            if ($isOverdue) {
                return 'bg-danger';
            }
            return 'bg-info';
        }
        
        return 'bg-secondary';
    }

    /**
     * Get status text for display
     */
    public function getStatusTextAttribute()
    {
        $now = Carbon::now();
        $isOverdue = $this->due_date < $now;
        $daysOverdue = $isOverdue ? (int) $now->diffInDays($this->due_date, false) : 0;
        
        // Jika sudah dibayar lunas
        if (($this->paid_amount ?? 0) >= $this->amount) {
            return 'Lunas';
        }
        
        // Jika belum dibayar sepeserpun
        if (!$this->paid_amount || $this->paid_amount == 0) {
            if ($isOverdue) {
                return "Belum Dibayar (Terlambat {$daysOverdue} hari)";
            }
            return 'Belum Dibayar';
        }
        
        // Jika sudah dibayar sebagian
        if ($this->paid_amount > 0 && $this->paid_amount < $this->amount) {
            if ($isOverdue) {
                return "Dibayar Sebagian (Terlambat {$daysOverdue} hari)";
            }
            return 'Dibayar Sebagian';
        }
        
        return 'Belum Dibayar';
    }
}
