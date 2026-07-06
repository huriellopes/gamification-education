<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student\Test;

use App\Actions\Student\SubmitTestAttemptAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\Test\SubmitTestRequest;
use App\Models\Subject;
use App\Models\Test;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class SubmitTestController extends Controller
{
    /**
     * Corrige e processa a tentativa de realização de teste enviada.
     */
    public function __invoke(
        SubmitTestRequest $request,
        Subject $subject,
        Test $test,
        SubmitTestAttemptAction $action,
    ): RedirectResponse {
        abort_if($test->subject_id !== $subject->id, 404);

        Gate::authorize('submit', $test);

        /** @var User $user */
        $user = Auth::user();

        /** @var array<int|string, mixed> $answers */
        $answers = $request->validated()['answers']; // [question_id => selected_option_index]

        $attempt = $action->execute($user, $test, $answers);

        // Feedback animado no frontend
        $correct = $attempt->correct_answers;
        $total = $attempt->total_questions;
        $score = $attempt->score; // Pontos obtidos

        return to_route('student.subjects.show', $subject)
            ->with('success', "Atividade enviada! Você acertou {$correct} de {$total} questões e obteve {$score} pontos nesta tentativa.");
    }
}
