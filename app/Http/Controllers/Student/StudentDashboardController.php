<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\ScoreHistory;
use App\Models\Subject;
use App\Models\TestAttempt;
use App\Models\User;
use App\Services\RankingService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class StudentDashboardController extends Controller
{
    protected RankingService $rankingService;

    public function __construct(RankingService $rankingService)
    {
        $this->rankingService = $rankingService;
    }

    public function index()
    {
        $user = Auth::user()->load('institution');

        // Se o usuário não tem uma instituição associada, mostramos uma lista vazia de matérias
        $subjects = collect();
        if ($user->institution_id) {
            $subjects = Subject::where('institution_id', $user->institution_id)
                ->with(['studyMaterials', 'tests'])
                ->get()
                ->map(function ($subject) use ($user) {
                    $totalMaterials = $subject->studyMaterials->count();

                    // Conta quantos materiais desta matéria o aluno concluiu
                    $completedMaterials = $user->completedMaterials()
                        ->where('subject_id', $subject->id)
                        ->count();

                    // Pega o melhor score do aluno nos testes desta matéria
                    $testIds = $subject->tests->pluck('id');
                    $bestScore = TestAttempt::where('user_id', $user->id)
                        ->whereIn('test_id', $testIds)
                        ->max('score') ?? 0;

                    return [
                        'id' => $subject->id,
                        'name' => $subject->name,
                        'description' => $subject->description,
                        'total_materials' => $totalMaterials,
                        'completed_materials' => $completedMaterials,
                        'best_test_score' => $bestScore,
                        'progress_percentage' => $totalMaterials > 0 ? round(($completedMaterials / $totalMaterials) * 100) : 0,
                    ];
                });
        }

        // Histórico recente de pontuações
        $scoreHistory = ScoreHistory::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Top 5 ranking global para exibir no widget lateral
        $leaderboardRaw = $this->rankingService->getGlobalRanking(5);
        /** @var array<int, array{position: int, name: string, points: int, institution: string}> $leaderboardItems */
        $leaderboardItems = [];
        foreach ($leaderboardRaw as $index => $rankUser) {
            /** @var User $rankUser */
            /** @var Institution|null $inst */
            $inst = $rankUser->institution;
            $leaderboardItems[] = [
                'position' => $index + 1,
                'name' => $rankUser->name,
                'points' => $rankUser->points,
                'institution' => $inst ? $inst->name : 'N/A',
            ];
        }
        $leaderboard = collect($leaderboardItems);

        // Estatísticas rápidas
        $stats = [
            'points' => $user->points,
            'completed_materials_count' => $user->completedMaterials()->count(),
            'test_attempts_count' => $user->testAttempts()->count(),
        ];

        return Inertia::render('Student/Dashboard', [
            'subjects' => $subjects,
            'scoreHistory' => $scoreHistory,
            'leaderboard' => $leaderboard,
            'stats' => $stats,
        ]);
    }
}
