<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SiteVisit;
use App\Services\SuperAdminDashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class IndexSiteVisitController extends Controller
{
    /**
     * Exibe as visitas ao site público no Super Admin.
     */
    public function __invoke(SuperAdminDashboardService $service): Response
    {
        return Inertia::render('SuperAdmin/SiteVisits', [
            'siteVisits' => $service->getSiteVisits(),
        ]);
    }

    /**
     * Exporta os logs de visitas do site para um arquivo XLSX (.xlsx).
     */
    public function export(Request $request): BinaryFileResponse
    {
        $visits = SiteVisit::orderBy('visited_at', 'desc')->get();

        $data = [
            ['ID', 'Data/Hora', 'Endereço IP (Descriptografado)', 'Navegador/Dispositivo']
        ];

        foreach ($visits as $visit) {
            $data[] = [
                (string) $visit->id,
                $visit->visited_at ? $visit->visited_at->format('d/m/Y H:i:s') : '',
                (string) $visit->ip_address,
                (string) $visit->user_agent,
            ];
        }

        $filename = 'visitas_site_' . date('Y_m_d_His') . '.xlsx';
        $filePath = tempnam(sys_get_temp_dir(), 'xlsx');

        if ($filePath === false) {
            abort(500, 'Não foi possível gerar o arquivo temporário de exportação.');
        }

        \Shuchkin\SimpleXLSXGen::fromArray($data)->saveAs($filePath);

        return response()->download($filePath, $filename)->deleteFileAfterSend(true);
    }
}
