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

class StoreQuestionController extends Controller
{
    /**
     * Cadastra uma nova questão no teste.
     */
    public function __invoke(QuestionData $data, Test $test): RedirectResponse
    {
        /** @var Subject $subject */
        $subject = $test->subject;
        Gate::authorize('manageContent', $subject);

        $attributes = $data->toArray();
        $attributes['test_id'] = $test->id;

        Question::create($attributes);

        return redirect()->back()->with('success', 'Questão cadastrada com sucesso!');
    }
}
