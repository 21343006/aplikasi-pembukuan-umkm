<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'business_name',
        'phone',
        'address',
        'business_type',
        'initial_balance',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'initial_balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the user's full business info.
     */
    public function getBusinessInfoAttribute(): string
    {
        return $this->business_name . ($this->business_type ? " ({$this->business_type})" : '');
    }

    /**
     * Scope untuk user aktif.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Format nomor telepon.
     */
    public function getFormattedPhoneAttribute(): ?string
    {
        if (!$this->phone) {
            return null;
        }

        // Format nomor telepon Indonesia
        $phone = preg_replace('/[^0-9]/', '', $this->phone);
        
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        } elseif (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }

        return '+' . $phone;
    }

    // Relationships
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function incomes()
    {
        return $this->hasMany(Income::class);
    }

    public function expenditures()
    {
        return $this->hasMany(Expenditure::class);
    }

    public function capitals()
    {
        return $this->hasMany(Capital::class);
    }

    public function fixedCosts()
    {
        return $this->hasMany(FixedCost::class);
    }

    public function debts()
    {
        return $this->hasMany(Debt::class);
    }

    public function receivables()
    {
        return $this->hasMany(Receivable::class);
    }

    public function stockHistories()
    {
        return $this->hasMany(StockHistory::class);
    }

    public function capitalearly()
    {
        return $this->hasOne(Capitalearly::class);
    }
}