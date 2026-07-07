<?php

declare(strict_types=1);

namespace App\Actions\Teacher;

use App\Models\Classroom;
use App\Models\User;

class EnrollStudentsInClassroomAction
{
    /**
     * Matricula os alunos informados na turma, considerando apenas usuários
     * que são estudantes E pertencem à mesma instituição da turma.
     *
     * @param  array<int, int>  $studentIds
     * @return int Quantidade de alunos efetivamente matriculados nesta chamada.
     */
    public function __invoke(Classroom $classroom, array $studentIds): int
    {
        $validIds = User::query()
            ->students()
            ->whereIn('id', $studentIds)
            ->where('institution_id', $classroom->institution_id)
            ->pluck('id')
            ->all();

        if ($validIds === []) {
            return 0;
        }

        $result = $classroom->students()->syncWithoutDetaching($validIds);

        // `attached` traz apenas os vínculos novos (evita recontar já matriculados).
        return count($result['attached']);
    }
}
