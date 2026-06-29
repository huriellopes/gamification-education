<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin\Subject;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;

class DestroySubjectController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Subject $subject): RedirectResponse
    {
        $subject->delete();

        return back()->with('success', __('messages.subject_deleted'));
    }
}
