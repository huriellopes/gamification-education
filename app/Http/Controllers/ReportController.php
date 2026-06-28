<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Report;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    /**
     * Faz o download do relatório gerado e o remove do sistema.
     */
    public function download(Report $report): BinaryFileResponse
    {
        // Garante que o relatório pertence ao usuário autenticado ou é super admin
        if ($report->user_id !== auth()->id() && !auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        if ($report->status !== 'completed' || !$report->file_path || !file_exists($report->file_path)) {
            abort(404, 'Relatório não disponível ou ainda processando.');
        }

        $response = response()->download($report->file_path)->deleteFileAfterSend();

        // Deleta o registro do banco de dados (o download físico já está agendado para deleção)
        $report->delete();

        return $response;
    }
}
