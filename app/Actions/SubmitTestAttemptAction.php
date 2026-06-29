<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Question;
use App\Models\Test;
use App\Models\TestAttempt;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SubmitTestAttemptAction
{
    protected AddPointsToUserAction $addPointsAction;

    public function __construct(AddPointsToUserAction $addPointsAction)
    {
        $this->addPointsAction = $addPointsAction;
    }

    /**
     * Corrige o teste enviado pelo aluno, registra a tentativa e calcula a pontuação.
     *
     * @param  array  $answers  Array no formato [question_id => selected_option_index]
     */
    public function execute(User $user, Test $test, array $answers): TestAttempt
    {
        return DB::transaction(function () use ($user, $test, $answers) {
            $questions = $test->questions;
            $totalQuestions = $questions->count();
            $correctCount = 0;

            foreach ($questions as $question) {
                /** @var Question $question */
                $selected = $answers[$question->id] ?? null;

                if ($selected !== null && (int) $selected === (int) $question->correct_option_index) {
                    $correctCount++;
                }
            }

            // Calcula a pontuação proporcional aos acertos
            $maxPoints = $test->points_reward;
            $pointsEarned = $totalQuestions > 0 ? (int) round(($correctCount / $totalQuestions) * $maxPoints) : 0;

            // Busca a melhor pontuação anterior deste aluno neste teste
            $bestPreviousScore = TestAttempt::where('user_id', $user->id)
                ->where('test_id', $test->id)
                ->max('score') ?? 0;

            // Cria o registro da tentativa
            $attempt = TestAttempt::create([
                'user_id' => $user->id,
                'test_id' => $test->id,
                'score' => $pointsEarned,
                'correct_answers' => $correctCount,
                'total_questions' => $totalQuestions,
                'completed_at' => now(),
            ]);

            // Se o aluno melhorou a nota anterior, concede a diferença de pontos
            if ($pointsEarned > $bestPreviousScore) {
                $pointsDiff = $pointsEarned - $bestPreviousScore;
                $description = "Atividade: {$test->title} (" . ($bestPreviousScore > 0 ? 'Melhoria de nota' : 'Primeira tentativa') . ')';

                $this->addPointsAction->execute(
                    $user,
                    $pointsDiff,
                    'test',
                    $attempt->id,
                    $description,
                );
            }

            return $attempt;
        });
    }
}
