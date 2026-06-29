<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\SiteVisitExportService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportSiteVisitController extends Controller
{
    /**
     * Exporta os logs de visitas do site para um arquivo XLSX (.xlsx).
     */
    public function __invoke(SiteVisitExportService $service): BinaryFileResponse
    {
        $filePath = $service->generateXlsx();

        return response()->download($filePath, $service->filename())->deleteFileAfterSend(true);
    }
}
