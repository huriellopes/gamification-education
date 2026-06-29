<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin\Classroom;

use App\Actions\SuperAdmin\Classroom\DeleteClassroomAction;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use Illuminate\Http\RedirectResponse;

class DestroyClassroomController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Classroom $classroom, DeleteClassroomAction $delete): RedirectResponse
    {
        $delete($classroom);

        return back()->with('success', 'Turma removida com sucesso!');
    }
}
