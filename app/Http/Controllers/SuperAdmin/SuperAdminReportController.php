<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateReportJob;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SuperAdminReportController extends Controller
{
    /**
     * Solicita a geração do relatório de membros em segundo plano.
     */
    public function requestMembers(Request $request): RedirectResponse
    {
        $report = Report::create([
            'user_id' => auth()->id(),
            'name' => 'Relatório de Membros',
            'status' => 'pending',
        ]);

        GenerateReportJob::dispatch($report, 'members');

        return redirect()->back()->with('success', __('messages.report_generation_queued'));
    }

    /**
     * Solicita a geração do relatório de desempenho global em segundo plano.
     */
    public function requestPerformance(Request $request): RedirectResponse
    {
        $report = Report::create([
            'user_id' => auth()->id(),
            'name' => 'Relatório de Desempenho Global',
            'status' => 'pending',
        ]);

        GenerateReportJob::dispatch($report, 'performance');

        return redirect()->back()->with('success', __('messages.report_generation_queued'));
    }
}
