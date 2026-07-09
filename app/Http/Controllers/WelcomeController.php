<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Public\RecordSiteVisitAction;
use App\Models\AppSetting;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class WelcomeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(RecordSiteVisitAction $recordVisit): Response
    {
        $recordVisit(request()->ip() ?? '127.0.0.1', request()->userAgent());

        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
            'stats' => $this->stats(),
        ]);
    }

    /**
     * Métricas da landing. Por padrão exibe números de vitrine (marketing);
     * o super admin pode desligar (`public_fake_metrics`) para mostrar os dados
     * reais — útil quando a base de usuários já é grande.
     *
     * @return list<array{value: string, label: string}>
     */
    private function stats(): array
    {
        if (AppSetting::bool('public_fake_metrics', true)) {
            return [
                ['value' => '15K+', 'label' => 'active_students'],
                ['value' => '320+', 'label' => 'subjects_offered'],
                ['value' => '4.8M+', 'label' => 'xp_earned'],
                ['value' => '98%', 'label' => 'satisfaction'],
            ];
        }

        $totalXp = User::studentsTotalXp();
        $formattedXp = $totalXp >= 1000 ? round($totalXp / 1000, 1) . 'k' : (string) $totalXp;

        return [
            ['value' => (string) User::activeStudentsCount(), 'label' => 'active_students'],
            ['value' => (string) Subject::activeCount(), 'label' => 'subjects_offered'],
            ['value' => $formattedXp, 'label' => 'xp_earned'],
            ['value' => '100%', 'label' => 'online_responsive'],
        ];
    }
}
