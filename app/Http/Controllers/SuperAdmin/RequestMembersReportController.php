<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Actions\SuperAdmin\Report\RequestReportGenerationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\Report\RequestMembersReportRequest;
use Illuminate\Http\RedirectResponse;

class RequestMembersReportController extends Controller
{
    /**
     * Solicita a geração do relatório de membros em segundo plano.
     */
    public function __invoke(
        RequestMembersReportRequest $request,
        RequestReportGenerationAction $requestReport,
    ): RedirectResponse {
        $requestReport('Relatório de Membros', 'members');

        return redirect()->back()->with('success', __('messages.report_generation_queued'));
    }
}
