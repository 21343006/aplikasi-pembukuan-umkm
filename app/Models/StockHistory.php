<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'type',
        'quantity_change',
        'quantity_before',
        'quantity_after',
        'description',
        'reference_type',
        'reference_id',
    ];

    protected $casts = [
        'quantity_change' => 'integer',
        'quantity_before' => 'integer',
        'quantity_after' => 'integer',
    ];

    // Relasi ke Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke referensi (Income, dll)
    public function reference()
    {
        return $this->morphTo();
    }

    // Scope untuk filter berdasarkan tipe
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Scope untuk filter berdasarkan produk
    public function scopeOfProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    // Method untuk mendapatkan label tipe
    public function getTypeLabelAttribute()
    {
        return match($this->type) {
            'in' => 'Stok Masuk',
            'out' => 'Stok Keluar',
            'adjustment' => 'Penyesuaian',
            'initial' => 'Stok Awal',
            default => 'Tidak Diketahui'
        };
    }

    // Method untuk mendapatkan warna badge berdasarkan tipe
    public function getTypeBadgeAttribute()
    {
        return match($this->type) {
            'in' => 'success',
            'out' => 'danger',
            'adjustment' => 'warning',
            'initial' => 'info',
            default => 'secondary'
        };
    }

    // Method untuk mendapatkan format quantity change
    public function getFormattedQuantityChangeAttribute()
    {
        $prefix = $this->quantity_change > 0 ? '+' : '';
        return $prefix . $this->quantity_change;
    }
}
