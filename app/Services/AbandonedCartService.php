<?php

namespace App\Services;

use App\Jobs\SendAbandonedCartRecoveryEmail;
use App\Models\CartItem;
use App\Models\CartRecoveryReminder;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AbandonedCartService
{
    public function markRecovered(User $user, string $reason = 'cart_updated'): void
    {
        CartRecoveryReminder::query()
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending', 'sent'])
            ->update([
                'status' => 'recovered',
                'recovered_at' => now(),
                'recovered_reason' => $reason,
            ]);
    }

    public function markRecoveredByToken(User $user, string $token): void
    {
        CartRecoveryReminder::query()
            ->where('user_id', $user->id)
            ->where('token', $token)
            ->whereIn('status', ['pending', 'sent'])
            ->update([
                'status' => 'recovered',
                'recovered_at' => now(),
                'recovered_reason' => 'email_recovery_visit',
            ]);
    }

    public function findReminderByToken(string $token): ?CartRecoveryReminder
    {
        return CartRecoveryReminder::query()
            ->where('token', $token)
            ->first();
    }

    public function scheduleReminders(int $minutes = 60): int
    {
        $threshold = now()->subMinutes($minutes);

        $staleUserRows = DB::table('cart_items')
            ->select('user_id', DB::raw('MAX(updated_at) as last_cart_activity_at'))
            ->groupBy('user_id')
            ->havingRaw('MAX(updated_at) <= ?', [$threshold])
            ->get();

        $scheduled = 0;

        foreach ($staleUserRows as $row) {
            $user = User::query()
                ->with(['cartItems.product'])
                ->find($row->user_id);

            if (! $user || ! $user->email || $user->cartItems->isEmpty()) {
                continue;
            }

            $lastActivityAt = $user->cartItems->max('updated_at');

            if (! $lastActivityAt || $lastActivityAt->gt($threshold)) {
                continue;
            }

            $alreadyQueued = CartRecoveryReminder::query()
                ->where('user_id', $user->id)
                ->where('last_cart_activity_at', $lastActivityAt)
                ->whereIn('status', ['pending', 'sent'])
                ->exists();

            if ($alreadyQueued) {
                continue;
            }

            $reminder = CartRecoveryReminder::query()->create([
                'user_id' => $user->id,
                'email' => $user->email,
                'token' => Str::random(40),
                'status' => 'pending',
                'last_cart_activity_at' => $lastActivityAt,
                'queued_at' => now(),
                'cart_snapshot' => $this->buildSnapshot($user->cartItems),
            ]);

            SendAbandonedCartRecoveryEmail::dispatch($reminder->id);
            $scheduled++;
        }

        return $scheduled;
    }

    /**
     * @param  Collection<int, CartItem>  $cartItems
     * @return array<int, array<string, mixed>>
     */
    protected function buildSnapshot(Collection $cartItems): array
    {
        return $cartItems->map(function (CartItem $item) {
            return [
                'product_id' => $item->product_id,
                'name' => $item->product?->name,
                'quantity' => $item->quantity,
                'price' => $item->product?->price,
                'image_url' => $item->product?->image_url,
            ];
        })->values()->all();
    }
}
