<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\Student;

use App\Http\Controllers\Controller;
use App\Models\ScoreHistory;
use App\Models\TestAttempt;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Inertia\Inertia;
use Inertia\Response;

class ShowStudentPerformanceController extends Controller
{
    /**
     * Exibe o desempenho detalhado de um estudante.
     */
    public function __invoke(User $student): Response
    {
        /** @var User $user */
        $user = auth()->user();

        // O professor só acessa o desempenho de estudantes matriculados em uma
        // de suas turmas (consistente com a listagem de "Meus Alunos").
        $isOwnStudent = $student->isStudent() && $student->enrolledClassrooms()
            ->where('teacher_id', $user->id)
            ->exists();

        abort_unless($isOwnStudent, 403);

        $student->load([
            'testAttempts.test',
        ]);

        // Carrega o histórico de pontos
        $scoreHistory = ScoreHistory::where('user_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->get();

        /** @var Collection<int, TestAttempt> $attempts */
        $attempts = $student->getAttribute('testAttempts');

        return Inertia::render('Teacher/Students/Show', [
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'points' => $student->points,
                'is_active' => $student->is_active,
                'attempts' => $attempts->map(function (TestAttempt $att) {
                    return [
                        'id' => $att->id,
                        'test_title' => $att->test->title ?? 'N/A',
                        'score' => $att->score,
                        'correct_answers' => $att->correct_answers,
                        'total_questions' => $att->total_questions,
                        'completed_at' => $att->completed_at instanceof Carbon ? $att->completed_at->format('d/m/Y H:i') : 'N/A',
                    ];
                }),
                'score_history' => $scoreHistory->map(function (ScoreHistory $hist) {
                    return [
                        'id' => $hist->id,
                        'points' => $hist->points,
                        'description' => $hist->description,
                        'created_at' => $hist->created_at instanceof Carbon ? $hist->created_at->format('d/m/Y H:i') : 'N/A',
                    ];
                }),
            ],
        ]);
    }
}
