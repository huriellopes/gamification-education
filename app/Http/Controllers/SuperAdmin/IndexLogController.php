<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\SuperAdminDashboardService;
use App\Services\System\LogFileService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexLogController extends Controller
{
    /**
     * Exibe os logs do sistema e failed jobs para o Super Admin.
     */
    public function __invoke(Request $request, SuperAdminDashboardService $service, LogFileService $logs): Response
    {
        return Inertia::render('SuperAdmin/Logs', [
            'logs' => $logs->list(),
            'selectedLog' => $logs->read($request->input('log_file')),
            'failedJobs' => $service->getFailedJobs(),
        ]);
    }
}
