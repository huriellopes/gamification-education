<?php

declare(strict_types=1);

namespace App\Http\Controllers\Report;

use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadReportController extends Controller
{
    /**
     * Faz o download do relatório gerado e o remove do sistema.
     */
    public function __invoke(Report $report): BinaryFileResponse
    {
        Gate::authorize('download', $report);

        if ($report->status !== ReportStatus::COMPLETED || !$report->file_path || !file_exists($report->file_path)) {
            abort(404, 'Relatório não disponível ou ainda processando.');
        }

        $response = response()->download($report->file_path)->deleteFileAfterSend();

        // Deleta o registro do banco de dados (o download físico já está agendado para deleção)
        $report->delete();

        return $response;
    }
}
