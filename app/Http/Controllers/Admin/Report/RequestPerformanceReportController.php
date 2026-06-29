<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Report;

use App\Actions\Admin\RequestPerformanceReportAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Report\RequestPerformanceReportRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class RequestPerformanceReportController extends Controller
{
    /**
     * Solicita a geração do relatório de desempenho da instituição em segundo plano.
     */
    public function __invoke(
        RequestPerformanceReportRequest $request,
        RequestPerformanceReportAction $requestReport,
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();

        $requestReport((int) $user->id, (int) $user->institution_id);

        return redirect()->back()->with('success', __('messages.report_generation_queued'));
    }
}
