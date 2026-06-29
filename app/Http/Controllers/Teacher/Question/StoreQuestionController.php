<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\Question;

use App\Actions\Teacher\CreateQuestionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\Question\StoreQuestionRequest;
use App\Models\Subject;
use App\Models\Test;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class StoreQuestionController extends Controller
{
    /**
     * Cadastra uma nova questão no teste.
     */
    public function __invoke(StoreQuestionRequest $request, Test $test, CreateQuestionAction $createQuestion): RedirectResponse
    {
        /** @var Subject $subject */
        $subject = $test->subject;
        Gate::authorize('manageContent', $subject);

        $createQuestion($request->validated(), $test);

        return back()->with('success', 'Questão cadastrada com sucesso!');
    }
}
