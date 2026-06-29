<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin\Classroom;

use App\Actions\SuperAdmin\Classroom\ToggleClassroomStatusAction;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use Illuminate\Http\RedirectResponse;

class ToggleClassroomStatusController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Classroom $classroom, ToggleClassroomStatusAction $toggle): RedirectResponse
    {
        $toggle($classroom);

        return back()->with('success', 'Status da turma atualizado!');
    }
}
