<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin\Subject;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\Subject\StoreSubjectRequest;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;

class StoreSubjectController extends Controller
{
    public function __invoke(StoreSubjectRequest $request): RedirectResponse
    {
        Subject::create($request->validated());

        return back()->with('success', __('messages.subject_created'));
    }
}
