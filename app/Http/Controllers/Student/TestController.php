<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Actions\SubmitTestAttemptAction;
use App\Data\Student\SubmitTestData;
use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Test;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TestController extends Controller
{
    /**
     * Exibe a interface de realização do teste.
     */
    public function show(Subject $subject, Test $test): Response
    {
        abort_if($test->subject_id !== $subject->id, 404);

        Gate::authorize('view', $test);

        // Carrega as questões sem enviar o índice correto para o cliente (segurança)
        $questions = $test->questions()
            ->get()
            ->map(function ($question) {
                /** @var Question $question */
                return [
                    'id' => $question->id,
                    'question_text' => $question->question_text,
                    'options' => $question->options,
                ];
            });

        return Inertia::render('Student/Tests/Show', [
            'subject' => $subject,
            'test' => [
                'id' => $test->id,
                'title' => $test->title,
                'description' => $test->description,
                'points_reward' => $test->points_reward,
                'questions' => $questions,
            ],
        ]);
    }

    /**
     * Corrige e processa a tentativa de realização de teste enviada.
     */
    public function submit(
        Subject $subject,
        Test $test,
        SubmitTestData $data,
        SubmitTestAttemptAction $action,
    ): RedirectResponse {
        abort_if($test->subject_id !== $subject->id, 404);

        Gate::authorize('submit', $test);

        /** @var User $user */
        $user = Auth::user();
        $answers = $data->answers; // [question_id => selected_option_index]

        $attempt = $action->execute($user, $test, $answers);

        // Feedback animado no frontend
        $correct = $attempt->correct_answers;
        $total = $attempt->total_questions;
        $score = $attempt->score; // Pontos obtidos

        return redirect()->route('student.subjects.show', $subject)
            ->with('success', "Atividade enviada! Você acertou {$correct} de {$total} questões e obteve {$score} pontos nesta tentativa.");
    }
}
