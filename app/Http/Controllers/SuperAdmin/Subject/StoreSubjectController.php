<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin\Subject;

use App\Actions\SuperAdmin\Subject\CreateSubjectAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\Subject\StoreSubjectRequest;
use Illuminate\Http\RedirectResponse;

class StoreSubjectController extends Controller
{
    public function __invoke(StoreSubjectRequest $request, CreateSubjectAction $createSubject): RedirectResponse
    {
        $createSubject($request->validated());

        return back()->with('success', __('messages.subject_created'));
    }
}
