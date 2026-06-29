<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student\Test;

use App\Http\Controllers\Controller;
use App\Http\Resources\Student\SubjectResource;
use App\Http\Resources\Student\TestResource;
use App\Models\Subject;
use App\Models\Test;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ShowTestController extends Controller
{
    /**
     * Exibe a interface de realização do teste.
     */
    public function __invoke(Subject $subject, Test $test): Response
    {
        abort_if($test->subject_id !== $subject->id, 404);

        Gate::authorize('view', $test);

        return Inertia::render('Student/Tests/Show', [
            'subject' => (new SubjectResource($subject))->resolve(),
            'test' => (new TestResource($test))->resolve(),
        ]);
    }
}
