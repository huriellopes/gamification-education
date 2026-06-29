<?php

declare(strict_types=1);

namespace App\Actions\SuperAdmin\Report;

use App\Jobs\GenerateReportJob;
use App\Models\Report;

class RequestReportGenerationAction
{
    /**
     * Cria um registro de relatório pendente e despacha o job de geração.
     */
    public function __invoke(string $name, string $type): Report
    {
        $report = Report::create([
            'user_id' => auth()->id(),
            'name' => $name,
            'status' => 'pending',
        ]);

        GenerateReportJob::dispatch($report, $type);

        return $report;
    }
}
