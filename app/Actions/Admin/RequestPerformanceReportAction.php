<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Jobs\GenerateReportJob;
use App\Models\Report;

class RequestPerformanceReportAction
{
    /**
     * Cria o registro do relatório e despacha sua geração em segundo plano.
     */
    public function __invoke(int $userId, int $institutionId): Report
    {
        $report = Report::create([
            'user_id' => $userId,
            'name' => 'Relatório de Desempenho da Instituição',
            'status' => 'pending',
        ]);

        GenerateReportJob::dispatch($report, 'performance', $institutionId);

        return $report;
    }
}
