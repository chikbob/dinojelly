<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GiftCardRedemption extends Model
{
    use HasFactory;

    protected $fillable = [
        'gift_card_id',
        'user_id',
        'order_id',
        'amount',
        'redeemed_at',
        'meta',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'redeemed_at' => 'datetime',
        'meta' => 'array',
    ];

    public function giftCard(): BelongsTo
    {
        return $this->belongsTo(GiftCard::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
