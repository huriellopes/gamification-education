<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin\Subject;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\Subject\UpdateSubjectRequest;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;

class UpdateSubjectController extends Controller
{
    public function __invoke(UpdateSubjectRequest $request, Subject $subject): RedirectResponse
    {
        $subject->update($request->validated());

        return back()->with('success', __('messages.subject_updated'));
    }
}
