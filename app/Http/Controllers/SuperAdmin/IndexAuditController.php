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
            // Teto de 100: valores negativos continuam acionando o modo
            // "carregar tudo" do serviço (comportamento documentado), mas um
            // per_page muito grande não força mais buscar a tabela inteira.
            'per_page' => min((int) $request->input('per_page', 20), 100),
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
