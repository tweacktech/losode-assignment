<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'name',
        'description',
        'price',
        'stock_quantity',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }


    public function orders()
    {
        return $this->hasMany(Order::class);
    }


    public function hasStock(int $quantity = 1): bool
    {
        return $this->stock_quantity >= $quantity;
    }


    public function isAvailable(): bool
    {
        return $this->status === 'active' && $this->stock_quantity > 0;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }


    public function scopeSearch($query, string $term)
    {
        return $query->where('name', 'like', "%{$term}%")
            ->orWhere('description', 'like', "%{$term}%");
    }
}
