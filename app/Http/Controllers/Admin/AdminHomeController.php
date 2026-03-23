<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminAnalyticsService;
use Inertia\Inertia;

class AdminHomeController extends Controller
{
    public function __construct(
        protected AdminAnalyticsService $adminAnalyticsService,
    ) {
    }

    public function index()
    {
        return Inertia::render('admin/Dashboard', $this->adminAnalyticsService->getDashboardPayload());
    }
}
