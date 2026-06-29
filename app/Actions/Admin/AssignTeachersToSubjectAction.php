<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\Subject;

class AssignTeachersToSubjectAction
{
    /**
     * Sincroniza os professores associados a uma matéria.
     *
     * @param  array<int, int>  $teacherIds
     */
    public function __invoke(Subject $subject, array $teacherIds): void
    {
        $subject->teachers()->sync($teacherIds);
    }
}
