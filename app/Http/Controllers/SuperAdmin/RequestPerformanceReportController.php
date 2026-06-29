<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Actions\SuperAdmin\Report\RequestReportGenerationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\Report\RequestPerformanceReportRequest;
use Illuminate\Http\RedirectResponse;

class RequestPerformanceReportController extends Controller
{
    /**
     * Solicita a geração do relatório de desempenho global em segundo plano.
     */
    public function __invoke(
        RequestPerformanceReportRequest $request,
        RequestReportGenerationAction $requestReport,
    ): RedirectResponse {
        $requestReport('Relatório de Desempenho Global', 'performance');

        return back()->with('success', __('messages.report_generation_queued'));
    }
}
