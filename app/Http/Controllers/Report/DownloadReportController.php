<?php

declare(strict_types=1);

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadReportController extends Controller
{
    /**
     * Faz o download do relatório gerado e o remove do sistema.
     */
    public function __invoke(Report $report): BinaryFileResponse
    {
        /** @var User $user */
        $user = auth()->user();

        // Garante que o relatório pertence ao usuário autenticado ou é super admin
        if ($report->user_id !== $user->id && !$user->isSuperAdmin()) {
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
