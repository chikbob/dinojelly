<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    use HasFactory;

    protected $fillable = [
        'referrer_user_id',
        'referred_user_id',
        'order_id',
        'code',
        'status',
        'referred_email',
        'reward_amount',
        'rewarded_at',
        'meta',
    ];

    protected $casts = [
        'reward_amount' => 'decimal:2',
        'rewarded_at' => 'datetime',
        'meta' => 'array',
    ];

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_user_id');
    }

    public function referredUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
