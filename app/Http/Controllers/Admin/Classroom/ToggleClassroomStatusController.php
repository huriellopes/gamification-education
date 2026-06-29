<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Classroom;

use App\Actions\Admin\ToggleClassroomStatusAction;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ToggleClassroomStatusController extends Controller
{
    /**
     * Ativa/Desativa uma turma da instituição do administrador.
     */
    public function __invoke(Classroom $classroom, ToggleClassroomStatusAction $toggleStatus): RedirectResponse
    {
        Gate::authorize('update', $classroom);

        $toggleStatus($classroom);

        return back()->with('success', 'Status da turma atualizado!');
    }
}
