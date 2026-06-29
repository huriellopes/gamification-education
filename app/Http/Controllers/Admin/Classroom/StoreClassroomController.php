<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Classroom;

use App\Actions\Admin\PersistClassroomAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Classroom\StoreClassroomRequest;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class StoreClassroomController extends Controller
{
    /**
     * Cria uma nova turma na instituição do administrador.
     */
    public function __invoke(StoreClassroomRequest $request, PersistClassroomAction $persist): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        /** @var array{name: string, description?: string|null, teacher_id?: int|null, subject_ids?: array<int, int>|null} $attributes */
        $attributes = $request->validated();

        $persist(new Classroom(), $attributes, (int) $user->institution_id);

        return back()->with('success', 'Turma criada com sucesso!');
    }
}
