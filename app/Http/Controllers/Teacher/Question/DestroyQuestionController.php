<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\Question;

use App\Actions\Teacher\DeleteQuestionAction;
use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Test;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class DestroyQuestionController extends Controller
{
    /**
     * Exclui uma questão.
     */
    public function __invoke(Question $question, DeleteQuestionAction $deleteQuestion): RedirectResponse
    {
        /** @var Test $test */
        $test = $question->test;
        /** @var Subject $subject */
        $subject = $test->subject;
        Gate::authorize('manageContent', $subject);

        $deleteQuestion($question);

        return redirect()->back()->with('success', 'Questão excluída com sucesso!');
    }
}
