<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\System\AuditLogService;
use Inertia\Inertia;
use Inertia\Response;

class IndexAuditController extends Controller
{
    /**
     * Exibe o log de auditoria da plataforma para o Super Admin.
     */
    public function __invoke(AuditLogService $audits): Response
    {
        return Inertia::render('SuperAdmin/Audits', [
            'audits' => $audits->recent(),
        ]);
    }
}
