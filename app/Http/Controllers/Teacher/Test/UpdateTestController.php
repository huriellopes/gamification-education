<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\Test;

use App\Data\Teacher\TestData;
use App\Http\Controllers\Controller;
use App\Models\Test;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class UpdateTestController extends Controller
{
    /**
     * Atualiza um teste.
     */
    public function __invoke(TestData $data, Test $test): RedirectResponse
    {
        $subject = $test->subject;
        Gate::authorize('manageContent', $subject);

        $attributes = $data->toArray();
        unset($attributes['subject_id']);
        $test->update($attributes);

        return redirect()->back()->with('success', 'Teste atualizado com sucesso!');
    }
}
