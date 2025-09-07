<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'quantity',
        'low_stock_threshold',
        'cost_per_unit',
        'selling_price',
        'unit',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'low_stock_threshold' => 'integer',
        'cost_per_unit' => 'decimal:2',
        'selling_price' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class);
    }

    public function stockHistories(): HasMany
    {
        return $this->hasMany(StockHistory::class);
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

    /**
     * Get total sold quantity
     */
    public function getTotalSoldAttribute()
    {
        return $this->incomes()->sum('jumlah_terjual');
    }

    /**
     * Get available quantity (initial - sold)
     */
    public function getAvailableQuantityAttribute()
    {
        return max(0, $this->quantity - $this->total_sold);
    }

    /**
     * Check if product is low stock
     */
    public function getIsLowStockAttribute()
    {
        return $this->available_quantity <= $this->low_stock_threshold;
    }

    /**
     * Get total revenue from this product
     */
    public function getTotalRevenueAttribute()
    {
        return $this->incomes()->sum('total_pendapatan');
    }

    /**
     * Get total profit from this product
     */
    public function getTotalProfitAttribute()
    {
        return $this->incomes()->sum('laba');
    }

    /**
     * Scope untuk filter berdasarkan user yang sedang login
     */
    public function scopeForCurrentUser($query)
    {
        return $query->where('user_id', Auth::id());
    }

    /**
     * Scope untuk produk dengan stok rendah
     */
    public function scopeLowStock($query)
    {
        return $query->whereRaw('quantity - (SELECT COALESCE(SUM(jumlah_terjual), 0) FROM incomes WHERE incomes.product_id = products.id AND incomes.user_id = products.user_id) <= low_stock_threshold');
    }

    /**
     * Scope untuk produk yang habis stok
     */
    public function scopeOutOfStock($query)
    {
        return $query->whereRaw('quantity - (SELECT COALESCE(SUM(jumlah_terjual), 0) FROM incomes WHERE incomes.product_id = products.id AND incomes.user_id = products.user_id) <= 0');
    }

    /**
     * Update quantity based on sales
     */
    public function updateQuantityFromSales()
    {
        $totalSold = $this->incomes()->sum('jumlah_terjual');
        $this->quantity = max(0, $this->quantity - $totalSold);
        $this->save();
        
        return $this;
    }

    /**
     * Add stock (for restocking)
     */
    public function addStock($quantity, $description = 'Penambahan stok manual', $referenceType = null, $referenceId = null)
    {
        $quantityBefore = $this->quantity;
        $this->quantity += $quantity;
        $this->save();
        
        // Catat riwayat stok
        $this->recordStockHistory(
            'in',
            $quantity,
            $quantityBefore,
            $this->quantity,
            $description,
            $referenceType,
            $referenceId
        );
        
        return $this;
    }

    /**
     * Record stock history
     */
    public function recordStockHistory($type, $quantityChange, $quantityBefore, $quantityAfter, $description, $referenceType = null, $referenceId = null)
    {
        return $this->stockHistories()->create([
            'user_id' => Auth::id() ?? $this->user_id,
            'type' => $type,
            'quantity_change' => $quantityChange,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $quantityAfter,
            'description' => $description,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
        ]);
    }

    /**
     * Adjust stock (manual adjustment)
     */
    public function adjustStock($newQuantity, $description = 'Penyesuaian stok manual')
    {
        $quantityBefore = $this->quantity;
        $quantityChange = $newQuantity - $quantityBefore;
        $this->quantity = $newQuantity;
        $this->save();
        
        // Catat riwayat stok
        $this->recordStockHistory(
            'adjustment',
            $quantityChange,
            $quantityBefore,
            $this->quantity,
            $description
        );
        
        return $this;
    }

    /**
     * Get sales statistics
     */
    public function getSalesStats()
    {
        $incomes = $this->incomes();
        
        return [
            'total_sold' => $incomes->sum('jumlah_terjual'),
            'total_revenue' => $incomes->sum('total_pendapatan'),
            'total_profit' => $incomes->sum('laba'),
            'average_daily_sales' => $incomes->count() > 0 ? $incomes->sum('jumlah_terjual') / $incomes->count() : 0,
            'available_quantity' => $this->available_quantity,
            'is_low_stock' => $this->is_low_stock,
        ];
    }

    /**
     * Boot method untuk menghitung otomatis sebelum save
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            // Auto set user_id saat creating jika belum ada
            if (!$product->user_id && Auth::check()) {
                $product->user_id = Auth::id();
            }
        });

        static::created(function ($product) {
            // Catat riwayat stok awal saat produk dibuat
            if ($product->quantity > 0) {
                $product->recordStockHistory(
                    'initial',
                    $product->quantity,
                    0,
                    $product->quantity,
                    'Stok awal produk'
                );
            }
        });
    }
}