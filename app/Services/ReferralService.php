<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReferralService
{
    public const SESSION_KEY = 'referral_code';

    public function ensureReferralCode(User $user): User
    {
        if ($user->referral_code) {
            return $user;
        }

        do {
            $code = strtoupper(Str::random(8));
        } while (User::query()->where('referral_code', $code)->exists());

        $user->update([
            'referral_code' => $code,
        ]);

        return $user->refresh();
    }

    public function captureCode(string $code, Request $request): ?User
    {
        $referrer = User::query()
            ->where('referral_code', strtoupper(trim($code)))
            ->first();

        if (! $referrer) {
            return null;
        }

        $request->session()->put(self::SESSION_KEY, $referrer->referral_code);

        return $referrer;
    }

    public function attachPendingReferral(User $user, Request $request): ?Referral
    {
        if ($user->referred_by_user_id) {
            return null;
        }

        $code = $request->session()->pull(self::SESSION_KEY);
        if (! $code) {
            return null;
        }

        $referrer = User::query()
            ->where('referral_code', $code)
            ->first();

        if (! $referrer || $referrer->id === $user->id) {
            return null;
        }

        $user->update([
            'referred_by_user_id' => $referrer->id,
        ]);

        return Referral::query()->firstOrCreate(
            [
                'referrer_user_id' => $referrer->id,
                'referred_user_id' => $user->id,
            ],
            [
                'code' => $referrer->referral_code,
                'status' => 'pending',
                'referred_email' => $user->email,
                'reward_amount' => 300,
            ],
        );
    }

    public function completeForOrder(Order $order): void
    {
        $user = $order->user()->first();
        if (! $user || ! $user->referred_by_user_id) {
            return;
        }

        $priorCompletedOrders = Order::query()
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->where('id', '!=', $order->id)
            ->exists();

        if ($priorCompletedOrders) {
            return;
        }

        $referral = Referral::query()
            ->where('referrer_user_id', $user->referred_by_user_id)
            ->where('referred_user_id', $user->id)
            ->first();

        if (! $referral || $referral->status === 'rewarded') {
            return;
        }

        $referral->update([
            'status' => 'rewarded',
            'order_id' => $order->id,
            'rewarded_at' => now(),
            'meta' => array_merge($referral->meta ?? [], [
                'rewarded_order_total' => $order->total_price,
            ]),
        ]);

        $order->update([
            'referral_id' => $referral->id,
        ]);

        $referral->referrer()->increment('referral_credit_balance', (float) $referral->reward_amount);
    }

    /**
     * @return array<string, mixed>
     */
    public function getProfilePayload(User $user): array
    {
        $user = $this->ensureReferralCode($user);

        $referrals = Referral::query()
            ->with(['referredUser', 'order'])
            ->where('referrer_user_id', $user->id)
            ->latest()
            ->limit(10)
            ->get();

        return [
            'referralCode' => $user->referral_code,
            'referralLink' => route('referrals.capture', $user->referral_code),
            'referralCreditBalance' => (float) $user->referral_credit_balance,
            'referralStats' => [
                'total' => (int) $referrals->count(),
                'rewarded' => (int) $referrals->where('status', 'rewarded')->count(),
                'pending' => (int) $referrals->where('status', 'pending')->count(),
                'earned' => (float) $referrals->where('status', 'rewarded')->sum('reward_amount'),
            ],
            'referrals' => $referrals->map(fn (Referral $referral) => [
                'id' => $referral->id,
                'status' => $referral->status,
                'reward_amount' => (float) $referral->reward_amount,
                'rewarded_at' => $referral->rewarded_at,
                'created_at' => $referral->created_at,
                'referred_user' => $referral->referredUser ? [
                    'name' => $referral->referredUser->name,
                    'email' => $referral->referredUser->email,
                ] : null,
                'order_id' => $referral->order_id,
            ])->values(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getAdminOverview(): array
    {
        $referrals = Referral::query()
            ->with(['referrer', 'referredUser', 'order'])
            ->latest()
            ->paginate(20)
            ->through(fn (Referral $referral) => [
                'id' => $referral->id,
                'code' => $referral->code,
                'status' => $referral->status,
                'reward_amount' => (float) $referral->reward_amount,
                'referrer' => $referral->referrer?->name,
                'referred_user' => $referral->referredUser?->email ?? $referral->referred_email,
                'order_id' => $referral->order_id,
                'created_at' => $referral->created_at,
                'rewarded_at' => $referral->rewarded_at,
            ]);

        return [
            'referrals' => $referrals,
            'stats' => [
                'total' => (int) Referral::query()->count(),
                'rewarded' => (int) Referral::query()->where('status', 'rewarded')->count(),
                'pending' => (int) Referral::query()->where('status', 'pending')->count(),
                'credits_issued' => (float) Referral::query()->where('status', 'rewarded')->sum('reward_amount'),
            ],
        ];
    }
}
