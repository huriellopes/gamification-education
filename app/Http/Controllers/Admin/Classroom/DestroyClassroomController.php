<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Classroom;

use App\Actions\Admin\DeleteClassroomAction;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class DestroyClassroomController extends Controller
{
    /**
     * Remove uma turma da instituição do administrador.
     */
    public function __invoke(Classroom $classroom, DeleteClassroomAction $deleteClassroom): RedirectResponse
    {
        Gate::authorize('delete', $classroom);

        $deleteClassroom($classroom);

        return back()->with('success', 'Turma removida com sucesso!');
    }
}
