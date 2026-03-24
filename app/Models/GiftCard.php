<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GiftCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'message',
        'purchaser_user_id',
        'recipient_user_id',
        'order_id',
        'initial_amount',
        'balance',
        'currency',
        'issued_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'initial_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'issued_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function purchaser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'purchaser_user_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(GiftCardRedemption::class);
    }
}
