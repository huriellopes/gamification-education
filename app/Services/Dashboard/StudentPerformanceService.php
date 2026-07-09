<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Models\ScoreHistory;
use App\Models\TestAttempt;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Monta o payload de desempenho detalhado de um estudante (tentativas de prova
 * e histórico de pontos) para a tela de acompanhamento do professor.
 */
class StudentPerformanceService
{
    /**
     * @return array<string, mixed>
     */
    public function forStudent(User $student): array
    {
        $student->load(['testAttempts.test']);

        /** @var Collection<int, TestAttempt> $attempts */
        $attempts = $student->getAttribute('testAttempts');

        $scoreHistory = ScoreHistory::query()
            ->where('user_id', $student->id)
            ->latest()
            ->get();

        return [
            'id' => $student->id,
            'name' => $student->name,
            'email' => $student->email,
            'points' => $student->points,
            'is_active' => $student->is_active,
            'attempts' => $attempts->map(fn (TestAttempt $attempt) => [
                'id' => $attempt->id,
                'test_title' => $attempt->test->title ?? 'N/A',
                'score' => $attempt->score,
                'correct_answers' => $attempt->correct_answers,
                'total_questions' => $attempt->total_questions,
                'completed_at' => $attempt->completed_at instanceof Carbon
                    ? $attempt->completed_at->format('d/m/Y H:i')
                    : 'N/A',
            ])->values(),
            'score_history' => $scoreHistory->map(fn (ScoreHistory $history) => [
                'id' => $history->id,
                'points' => $history->points,
                'description' => $history->description,
                'created_at' => $history->created_at instanceof Carbon
                    ? $history->created_at->format('d/m/Y H:i')
                    : 'N/A',
            ])->values(),
        ];
    }
}
