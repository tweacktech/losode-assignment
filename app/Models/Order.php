<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'vendor_id',
        'quantity',
        'unit_price',
        'total_price',
        'status',
        'notes',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'product_id' => 'integer',
        'vendor_id' => 'integer',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function product()
    {
        return $this->belongsTo(Product::class);
    }


    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }


    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }


    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }


    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }


    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }


    public function isPending(): bool
    {
        return $this->status === 'pending';
    }


    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }
}
