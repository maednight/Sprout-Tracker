<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Contracts\View\View;

/**
 * Handles dashboard page requests.
 */
class DashboardController extends Controller
{
    /**
     * @var DashboardService Service used to build dashboard page data.
     */
    private DashboardService $dashboardService;

    /**
     * @param DashboardService $dashboardService Service used to build dashboard page data.
     */
    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(): View
    {
        $user = auth()->user();

        return view('public.home.dashboard', [
            'dashboardPayload' => $this->dashboardService->buildPayload($user->id),
        ]);
    }
}
