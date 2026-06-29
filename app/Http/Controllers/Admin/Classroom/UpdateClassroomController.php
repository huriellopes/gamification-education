<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Classroom;

use App\Actions\Admin\PersistClassroomAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Classroom\UpdateClassroomRequest;
use App\Models\Classroom;
use Illuminate\Http\RedirectResponse;

class UpdateClassroomController extends Controller
{
    /**
     * Atualiza uma turma da instituição do administrador.
     */
    public function __invoke(
        UpdateClassroomRequest $request,
        Classroom $classroom,
        PersistClassroomAction $persist,
    ): RedirectResponse {
        /** @var array{name: string, description?: string|null, teacher_id?: int|null, subject_ids?: array<int, int>|null} $attributes */
        $attributes = $request->validated();

        $persist($classroom, $attributes, (int) $classroom->institution_id);

        return back()->with('success', 'Turma atualizada com sucesso!');
    }
}
