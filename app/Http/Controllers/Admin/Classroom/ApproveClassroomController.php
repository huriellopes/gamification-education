<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Classroom;

use App\Actions\Admin\ApproveClassroomAction;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ApproveClassroomController extends Controller
{
    /**
     * Aprova uma turma pendente criada por um professor da instituição.
     */
    public function __invoke(Classroom $classroom, ApproveClassroomAction $approve): RedirectResponse
    {
        Gate::authorize('approve', $classroom);

        /** @var User $admin */
        $admin = auth()->user();

        $approve($classroom, $admin);

        return back()->with('success', __('classrooms.approved'));
    }
}
