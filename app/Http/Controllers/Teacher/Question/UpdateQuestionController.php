<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\Question;

use App\Actions\Teacher\UpdateQuestionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\Question\UpdateQuestionRequest;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Test;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class UpdateQuestionController extends Controller
{
    /**
     * Atualiza uma questão.
     */
    public function __invoke(UpdateQuestionRequest $request, Question $question, UpdateQuestionAction $updateQuestion): RedirectResponse
    {
        /** @var Test $test */
        $test = $question->test;
        /** @var Subject $subject */
        $subject = $test->subject;
        Gate::authorize('manageContent', $subject);

        $updateQuestion($question, $request->validated());

        return redirect()->back()->with('success', 'Questão atualizada com sucesso!');
    }
}
