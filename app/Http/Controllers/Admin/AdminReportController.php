<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateReportJob;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminReportController extends Controller
{
    /**
     * Solicita a geração do relatório de desempenho da instituição em segundo plano.
     */
    public function requestPerformance(Request $request): RedirectResponse
    {
        $institutionId = $request->user()->institution_id;

        $report = Report::create([
            'user_id' => auth()->id(),
            'name' => 'Relatório de Desempenho da Instituição',
            'status' => 'pending',
        ]);

        GenerateReportJob::dispatch($report, 'performance', $institutionId);

        return redirect()->back()->with('success', __('messages.report_generation_queued'));
    }
}
