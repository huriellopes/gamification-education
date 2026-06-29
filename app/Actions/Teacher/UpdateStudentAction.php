<?php

declare(strict_types=1);

namespace App\Actions\Teacher;

use App\Models\Classroom;
use App\Models\User;

class UpdateStudentAction
{
    /**
     * Atualiza um estudante, preservando a instituição e o papel.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function __invoke(User $student, array $attributes): User
    {
        unset($attributes['institution_id']);
        unset($attributes['role']);

        $hasClassroom = array_key_exists('classroom_id', $attributes);
        $classroomId = $attributes['classroom_id'] ?? null;
        unset($attributes['classroom_id']);

        if (empty($attributes['password'])) {
            unset($attributes['password']);
        } else {
            $attributes['password'] = bcrypt($attributes['password']);
        }

        $student->update($attributes);

        if ($hasClassroom) {
            $this->syncClassroom($student, $classroomId);
        }

        return $student;
    }

    /**
     * Sincroniza a turma do estudante (dentro da sua instituição).
     */
    private function syncClassroom(User $student, mixed $classroomId): void
    {
        if ($classroomId === null) {
            $student->enrolledClassrooms()->sync([]);

            return;
        }

        $isSameInstitution = Classroom::query()
            ->whereKey($classroomId)
            ->where('institution_id', $student->institution_id)
            ->exists();

        if ($isSameInstitution) {
            $student->enrolledClassrooms()->sync([(int) $classroomId]);
        }
    }
}
