<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin\Subject;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\SuperAdminDashboardService;
use Inertia\Inertia;
use Inertia\Response;

class IndexSubjectController extends Controller
{
    /**
     * Exibe a lista de matérias para o Super Admin.
     */
    public function __invoke(SuperAdminDashboardService $service): Response
    {
        return Inertia::render('SuperAdmin/Subjects', [
            'subjects' => $service->getSubjects(),
            'institutions' => $service->getInstitutions(),
        ]);
    }
}
