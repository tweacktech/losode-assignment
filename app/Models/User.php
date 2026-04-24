<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;


    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'city',
        'state',
        'postal_code',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    public function orders()
    {
        return $this->hasMany(Order::class);
    }


    public function isActive(): bool
    {
        return $this->is_active;
    }


    public function getOrderCountAttribute(): int
    {
        return $this->orders()->count();
    }


    public function getTotalSpentAttribute()
    {
        return $this->orders()
            ->where('status', 'completed')
            ->sum('total_price');
    }
}
