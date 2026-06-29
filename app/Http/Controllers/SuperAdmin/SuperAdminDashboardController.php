<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\SuperAdminDashboardService;
use Inertia\Inertia;
use Inertia\Response;

class SuperAdminDashboardController extends Controller
{
    /**
     * Handle the incoming request to show Super Admin main dashboard metrics and charts.
     */
    public function __invoke(SuperAdminDashboardService $service): Response
    {
        return Inertia::render('SuperAdmin/Dashboard', [
            'metrics' => $service->getMetrics(),
            'performanceChart' => $service->getPerformanceChart(),
            'siteVisitsChart' => $service->getSiteVisitsChart(),
        ]);
    }
}
