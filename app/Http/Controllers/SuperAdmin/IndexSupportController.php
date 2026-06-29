<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\SuperAdminDashboardService;
use Inertia\Inertia;
use Inertia\Response;

class IndexSupportController extends Controller
{
    /**
     * Exibe a lista de chamados de suporte para o Super Admin.
     */
    public function __invoke(SuperAdminDashboardService $service): Response
    {
        return Inertia::render('SuperAdmin/Supports', [
            'supports' => $service->getSupports(),
        ]);
    }
}
