<?php

namespace App\Http\Controllers;

use App\Services\AbandonedCartService;
use Illuminate\Http\Request;

class CartRecoveryController extends Controller
{
    public function __construct(
        protected AbandonedCartService $abandonedCartService,
    ) {}

    public function recover(string $token, Request $request)
    {
        $reminder = $this->abandonedCartService->findReminderByToken($token);

        abort_unless($reminder, 404);

        if (! $request->user()) {
            $request->session()->put('url.intended', route('cart.recover', $token));

            return redirect()->route('login');
        }

        abort_unless($request->user()->id === $reminder->user_id, 403);

        $this->abandonedCartService->markRecoveredByToken($request->user(), $token);

        return redirect()->route('cart.index', ['recovered' => 1]);
    }
}
