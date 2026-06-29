<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin\Subject;

use App\Data\SuperAdmin\Subject\SubjectData;
use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;

class UpdateSubjectController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(SubjectData $data, Subject $subject): RedirectResponse
    {
        $subject->update($data->toArray());

        return redirect()->back()->with('success', __('messages.subject_updated'));
    }
}
