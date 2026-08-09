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
            // Teto de 100: valores negativos continuam acionando o modo
            // "carregar tudo" do serviço (comportamento documentado), mas um
            // per_page muito grande não força mais buscar a tabela inteira.
            'per_page' => min((int) $request->input('per_page', 20), 100),
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
