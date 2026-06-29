<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\Test;

use App\Actions\Teacher\DeleteTestAction;
use App\Http\Controllers\Controller;
use App\Models\Test;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class DestroyTestController extends Controller
{
    /**
     * Exclui um teste.
     */
    public function __invoke(Test $test, DeleteTestAction $deleteTest): RedirectResponse
    {
        $subject = $test->subject;
        Gate::authorize('manageContent', $subject);

        $deleteTest($test);

        return redirect()->back()->with('success', 'Teste excluído com sucesso!');
    }
}
