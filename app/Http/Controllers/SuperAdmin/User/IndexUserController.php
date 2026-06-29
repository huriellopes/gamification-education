<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin\User;

use App\Http\Controllers\Controller;
use App\Services\SuperAdminDashboardService;
use Inertia\Inertia;
use Inertia\Response;

class IndexUserController extends Controller
{
    /**
     * Exibe a lista de usuários para o Super Admin.
     */
    public function __invoke(SuperAdminDashboardService $service): Response
    {
        return Inertia::render('SuperAdmin/Users', [
            'users' => $service->getUsers(),
            'institutions' => $service->getInstitutions(),
        ]);
    }
}
