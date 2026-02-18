<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
    ];

    protected $table = 'users';

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function favorites()
    {
        return $this->hasMany(\App\Models\Favorite::class);
    }

    public function favoriteProducts()
    {
        return $this->belongsToMany(\App\Models\Product::class, 'favorites');
    }

    public function orders()
    {
        return $this->hasMany(\App\Models\Order::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
