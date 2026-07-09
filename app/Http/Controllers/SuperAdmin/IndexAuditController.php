<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\System\AuditLogService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexAuditController extends Controller
{
    /**
     * Exibe o log de auditoria da plataforma para o Super Admin (paginado no servidor).
     */
    public function __invoke(Request $request, AuditLogService $audits): Response
    {
        $filters = [
            'search' => $request->filled('search') ? (string) $request->input('search') : null,
            'sort' => (string) $request->input('sort', 'created_at'),
            'direction' => (string) $request->input('direction', 'desc'),
            'per_page' => (int) $request->input('per_page', 20),
        ];

        return Inertia::render('SuperAdmin/Audits', [
            'audits' => $audits->paginated(
                $filters['per_page'],
                $filters['search'],
                $filters['sort'],
                $filters['direction'],
            )->withQueryString(),
            'filters' => $filters,
        ]);
    }
}
