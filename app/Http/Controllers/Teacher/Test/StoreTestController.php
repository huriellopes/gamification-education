<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\Test;

use App\Actions\Teacher\CreateTestAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\Test\StoreTestRequest;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class StoreTestController extends Controller
{
    /**
     * Cadastra um novo teste para a matéria.
     */
    public function __invoke(StoreTestRequest $request, Subject $subject, CreateTestAction $createTest): RedirectResponse
    {
        Gate::authorize('manageContent', $subject);

        $createTest($request->validated(), $subject);

        return redirect()->back()->with('success', 'Teste cadastrado com sucesso!');
    }
}
