<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartRecoveryReminder extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'token',
        'status',
        'last_cart_activity_at',
        'queued_at',
        'sent_at',
        'recovered_at',
        'recovered_reason',
        'cart_snapshot',
    ];

    protected $casts = [
        'last_cart_activity_at' => 'datetime',
        'queued_at' => 'datetime',
        'sent_at' => 'datetime',
        'recovered_at' => 'datetime',
        'cart_snapshot' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
