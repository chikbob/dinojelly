<?php

namespace App\Http\Controllers;

use App\Services\ReferralService;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    public function __construct(
        protected ReferralService $referralService,
    ) {
    }

    public function capture(string $code, Request $request)
    {
        $referrer = $this->referralService->captureCode($code, $request);

        if (!$referrer) {
            return redirect()->route('products.index')
                ->withErrors(['referral' => 'Реферальная ссылка недействительна']);
        }

        if ($request->user()) {
            $this->referralService->attachPendingReferral($request->user(), $request);
        }

        return redirect()->route('register')
            ->with('success', "Реферальный код {$referrer->referral_code} сохранен");
    }
}
