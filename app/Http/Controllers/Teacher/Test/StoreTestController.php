<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\Test;

use App\Data\Teacher\TestData;
use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Test;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class StoreTestController extends Controller
{
    /**
     * Cadastra um novo teste para a matéria.
     */
    public function __invoke(TestData $data, Subject $subject): RedirectResponse
    {
        Gate::authorize('manageContent', $subject);

        $attributes = $data->toArray();
        $attributes['subject_id'] = $subject->id;

        Test::create($attributes);

        return redirect()->back()->with('success', 'Teste cadastrado com sucesso!');
    }
}
