<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin\Institution;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\SuperAdminDashboardService;
use Inertia\Inertia;
use Inertia\Response;

class IndexInstitutionController extends Controller
{
    /**
     * Exibe a lista de instituições para o Super Admin.
     */
    public function __invoke(SuperAdminDashboardService $service): Response
    {
        return Inertia::render('SuperAdmin/Institutions', [
            'institutions' => $service->getInstitutions(),
        ]);
    }
}
