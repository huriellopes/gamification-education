<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\SuperAdminDashboardService;
use Inertia\Inertia;
use Inertia\Response;

class IndexReportController extends Controller
{
    /**
     * Exibe a lista de relatórios para o Super Admin.
     */
    public function __invoke(SuperAdminDashboardService $service): Response
    {
        return Inertia::render('SuperAdmin/Reports', [
            'reports' => $service->getReports((int) auth()->id()),
            'institutions' => $service->getInstitutions(),
        ]);
    }
}
