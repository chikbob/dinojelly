<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReferralService;
use Inertia\Inertia;

class ReferralController extends Controller
{
    public function __construct(
        protected ReferralService $referralService,
    ) {
    }

    public function index()
    {
        return Inertia::render('admin/Referrals/Index', $this->referralService->getAdminOverview());
    }
}
