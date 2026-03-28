<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    public function index(): View
    {
        $user = auth()->user();

        return view('public.home.dashboard', [
            'dashboardPayload' => $this->dashboardService->buildPayload($user->id),
        ]);
    }
}
