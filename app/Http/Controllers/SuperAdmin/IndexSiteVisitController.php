<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\SuperAdminDashboardService;
use Inertia\Inertia;
use Inertia\Response;

class IndexSiteVisitController extends Controller
{
    /**
     * Exibe as visitas ao site público no Super Admin.
     */
    public function __invoke(SuperAdminDashboardService $service): Response
    {
        return Inertia::render('SuperAdmin/SiteVisits', [
            'siteVisits' => $service->getSiteVisits(),
        ]);
    }
}
