<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin\Classroom;

use App\Actions\SuperAdmin\Classroom\PersistClassroomAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\Classroom\StoreClassroomRequest;
use App\Models\Classroom;
use Illuminate\Http\RedirectResponse;

class StoreClassroomController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(StoreClassroomRequest $request, PersistClassroomAction $persist): RedirectResponse
    {
        /** @var array{name: string, description: string|null, teacher_id: int|null, institution_id: int, subject_ids?: array<int, int>|null} $data */
        $data = $request->validated();

        $persist(new Classroom(), $data, (int) $data['institution_id']);

        return back()->with('success', 'Turma criada com sucesso!');
    }
}
