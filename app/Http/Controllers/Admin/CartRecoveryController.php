<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CartRecoveryReminder;
use Inertia\Inertia;

class CartRecoveryController extends Controller
{
    public function index()
    {
        $reminders = CartRecoveryReminder::query()
            ->with('user')
            ->latest()
            ->paginate(20)
            ->through(fn (CartRecoveryReminder $reminder) => [
                'id' => $reminder->id,
                'email' => $reminder->email,
                'status' => $reminder->status,
                'last_cart_activity_at' => $reminder->last_cart_activity_at,
                'queued_at' => $reminder->queued_at,
                'sent_at' => $reminder->sent_at,
                'recovered_at' => $reminder->recovered_at,
                'recovered_reason' => $reminder->recovered_reason,
                'items_count' => count($reminder->cart_snapshot ?? []),
                'user' => $reminder->user ? [
                    'id' => $reminder->user->id,
                    'name' => $reminder->user->name,
                    'email' => $reminder->user->email,
                ] : null,
            ]);

        return Inertia::render('admin/Recoveries/Index', [
            'reminders' => $reminders,
        ]);
    }
}
