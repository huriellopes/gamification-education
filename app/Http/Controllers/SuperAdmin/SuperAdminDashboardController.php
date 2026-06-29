<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\SuperAdminDashboardService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SuperAdminDashboardController extends Controller
{
    /**
     * Handle the incoming request to show Super Admin main dashboard metrics and charts.
     */
    public function __invoke(Request $request, SuperAdminDashboardService $service): Response
    {
        $metrics = $service->getMetrics();

        // 1. Gráfico de Desempenho Geral (XP obtido nos últimos 7 dias)
        $performanceChartRaw = \App\Models\ScoreHistory::selectRaw('DATE(created_at) as date, SUM(points) as total_points')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->mapWithKeys(fn ($item) => [Carbon::parse($item->date)->format('d/m') => (int) $item->total_points])
            ->all();

        $performanceChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $dayStr = now()->subDays($i)->format('d/m');
            $performanceChart[] = [
                'day' => $dayStr,
                'points' => $performanceChartRaw[$dayStr] ?? 0,
            ];
        }

        // 2. Gráfico de Visitas ao Site Público (Visitas nos últimos 7 dias)
        $visitsChartRaw = \App\Models\SiteVisit::selectRaw('DATE(visited_at) as date, COUNT(*) as total_visits')
            ->where('visited_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->mapWithKeys(fn ($item) => [Carbon::parse($item->date)->format('d/m') => (int) $item->total_visits])
            ->all();

        $siteVisitsChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $dayStr = now()->subDays($i)->format('d/m');
            $siteVisitsChart[] = [
                'day' => $dayStr,
                'visits' => $visitsChartRaw[$dayStr] ?? 0,
            ];
        }

        return Inertia::render('SuperAdmin/Dashboard', [
            'metrics' => $metrics,
            'performanceChart' => $performanceChart,
            'siteVisitsChart' => $siteVisitsChart,
        ]);
    }
}
