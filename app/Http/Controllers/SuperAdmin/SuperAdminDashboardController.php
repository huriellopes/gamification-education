<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\Health\SystemHealthService;
use App\Services\Dashboard\SuperAdminDashboardService;
use Inertia\Inertia;
use Inertia\Response;

class SuperAdminDashboardController extends Controller
{
    /**
     * Handle the incoming request to show Super Admin main dashboard metrics and charts.
     */
    public function __invoke(SuperAdminDashboardService $service, SystemHealthService $health): Response
    {
        return Inertia::render('SuperAdmin/Dashboard', [
            'metrics' => $service->getMetrics(),
            'health' => $health->report(),
            'performanceChart' => $service->getPerformanceChart(),
            'siteVisitsChart' => $service->getSiteVisitsChart(),
        ]);
    }
}
