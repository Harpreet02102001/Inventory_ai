<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * DashboardController
 *
 * Displays the single dashboard view — its contents are entirely
 * determined by the requesting user's permissions, resolved inside
 * DashboardService, so this controller stays a thin pass-through.
 */
class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboardService) {}

    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('dashboard', [
            'widgets'             => $this->dashboardService->getWidgets($user),
            'recentStockActivity' => $this->dashboardService->getRecentStockActivity($user),
        ]);
    }
}
