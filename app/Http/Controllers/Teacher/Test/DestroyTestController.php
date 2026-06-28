<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\Test;

use App\Http\Controllers\Controller;
use App\Models\Test;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class DestroyTestController extends Controller
{
    /**
     * Exclui um teste.
     */
    public function __invoke(Test $test): RedirectResponse
    {
        $subject = $test->subject;
        Gate::authorize('manageContent', $subject);

        $test->delete();

        return redirect()->back()->with('success', 'Teste excluído com sucesso!');
    }
}
