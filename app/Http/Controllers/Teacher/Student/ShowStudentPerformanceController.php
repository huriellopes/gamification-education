<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\Student;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Dashboard\StudentPerformanceService;
use Inertia\Inertia;
use Inertia\Response;

class ShowStudentPerformanceController extends Controller
{
    /**
     * Exibe o desempenho detalhado de um estudante.
     */
    public function __invoke(User $student, StudentPerformanceService $performance): Response
    {
        /** @var User $user */
        $user = auth()->user();

        // O professor só acessa o desempenho de estudantes matriculados em uma
        // de suas turmas (consistente com a listagem de "Meus Alunos").
        $isOwnStudent = $student->isStudent() && $student->enrolledClassrooms()
            ->where('teacher_id', $user->id)
            ->exists();

        abort_unless($isOwnStudent, 403);

        return Inertia::render('Teacher/Students/Show', [
            'student' => $performance->forStudent($student),
        ]);
    }
}
