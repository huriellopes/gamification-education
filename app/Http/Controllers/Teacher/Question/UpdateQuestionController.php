<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\Question;

use App\Data\Teacher\QuestionData;
use App\Http\Controllers\Controller;
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
    public function __invoke(QuestionData $data, Question $question): RedirectResponse
    {
        /** @var Test $test */
        $test = $question->test;
        /** @var Subject $subject */
        $subject = $test->subject;
        Gate::authorize('manageContent', $subject);

        $attributes = $data->toArray();
        unset($attributes['test_id']);
        $question->update($attributes);

        return redirect()->back()->with('success', 'Questão atualizada com sucesso!');
    }
}
