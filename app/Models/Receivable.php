<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Receivable extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'debtor_name',
        'description',
        'amount',
        'due_date',
        'status',
        'paid_amount',
        'paid_date',
        'notes',
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

    public function getRemainingAmountAttribute()
    {
        return $this->amount - $this->paid_amount;
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
        if ($this->paid_amount >= $this->amount) {
            return 'Lunas';
        }
        
        // Jika belum dibayar sepeserpun
        if ($this->paid_amount == 0) {
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
        if ($this->paid_amount >= $this->amount) {
            return 'bg-success';
        }
        
        // Jika belum dibayar sepeserpun
        if ($this->paid_amount == 0) {
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
        if ($this->paid_amount >= $this->amount) {
            return 'Lunas';
        }
        
        // Jika belum dibayar sepeserpun
        if ($this->paid_amount == 0) {
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
