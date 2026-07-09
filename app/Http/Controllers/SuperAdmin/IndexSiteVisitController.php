<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\SuperAdminDashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexSiteVisitController extends Controller
{
    /**
     * Exibe as visitas ao site público no Super Admin (paginado no servidor).
     */
    public function __invoke(Request $request, SuperAdminDashboardService $service): Response
    {
        $filters = [
            'search' => $request->filled('search') ? (string) $request->input('search') : null,
            'sort' => (string) $request->input('sort', 'visited_at'),
            'direction' => (string) $request->input('direction', 'desc'),
            'per_page' => (int) $request->input('per_page', 20),
        ];

        return Inertia::render('SuperAdmin/SiteVisits', [
            'siteVisits' => $service->getSiteVisits(
                $filters['per_page'],
                $filters['search'],
                $filters['sort'],
                $filters['direction'],
            )->withQueryString(),
            'filters' => $filters,
        ]);
    }
}
