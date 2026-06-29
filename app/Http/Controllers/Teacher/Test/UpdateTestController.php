<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\Test;

use App\Actions\Teacher\UpdateTestAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\Test\UpdateTestRequest;
use App\Models\Test;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class UpdateTestController extends Controller
{
    /**
     * Atualiza um teste.
     */
    public function __invoke(UpdateTestRequest $request, Test $test, UpdateTestAction $updateTest): RedirectResponse
    {
        $subject = $test->subject;
        Gate::authorize('manageContent', $subject);

        $updateTest($test, $request->validated());

        return redirect()->back()->with('success', 'Teste atualizado com sucesso!');
    }
}
