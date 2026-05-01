<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'subscription_id',
        'address_id',
        'delivery_slot_id',
        'promo_code_id',
        'gift_card_id',
        'referral_id',
        'total_price',
        'delivery_price',
        'discount_amount',
        'gift_card_amount',
        'referral_credit_amount',
        'gift_card_refunded_at',
        'referral_credit_refunded_at',
        'total_quantity',
        'payment_method',
        'status',
    ];

    protected $casts = [
        'gift_card_refunded_at' => 'datetime',
        'referral_credit_refunded_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function sourceSubscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'source_order_id');
    }

    public function deliverySlot(): BelongsTo
    {
        return $this->belongsTo(DeliverySlot::class);
    }

    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function giftCard(): BelongsTo
    {
        return $this->belongsTo(GiftCard::class);
    }

    public function referral(): BelongsTo
    {
        return $this->belongsTo(Referral::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function events(): HasMany
    {
        return $this->hasMany(OrderEvent::class)->latest();
    }
}
