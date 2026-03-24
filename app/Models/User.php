<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'referral_code',
        'referred_by_user_id',
        'referral_credit_balance',
    ];

    protected $table = 'users';

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'referral_credit_balance' => 'decimal:2',
    ];

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function favoriteProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'favorites');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function orderEvents(): HasMany
    {
        return $this->hasMany(OrderEvent::class, 'actor_user_id');
    }

    public function cartRecoveryReminders(): HasMany
    {
        return $this->hasMany(CartRecoveryReminder::class);
    }

    public function usedPromoCodes(): BelongsToMany
    {
        return $this->belongsToMany(PromoCode::class)
            ->withPivot(['order_id', 'used_at'])
            ->withTimestamps();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by_user_id');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'referrer_user_id');
    }

    public function referredByProgram(): HasMany
    {
        return $this->hasMany(Referral::class, 'referred_user_id');
    }

    public function giftCards(): HasMany
    {
        return $this->hasMany(GiftCard::class, 'recipient_user_id');
    }

    public function purchasedGiftCards(): HasMany
    {
        return $this->hasMany(GiftCard::class, 'purchaser_user_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
