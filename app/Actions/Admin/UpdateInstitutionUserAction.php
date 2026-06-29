<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Enums\UserRole;
use App\Models\Classroom;
use App\Models\User;

class UpdateInstitutionUserAction
{
    /**
     * Atualiza um membro da instituição, preservando a senha quando não informada.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function __invoke(User $user, array $attributes): User
    {
        unset($attributes['institution_id']);

        $hasClassroom = array_key_exists('classroom_id', $attributes);
        $classroomId = $attributes['classroom_id'] ?? null;
        unset($attributes['classroom_id']);

        if (empty($attributes['password'])) {
            unset($attributes['password']);
        } else {
            $attributes['password'] = bcrypt($attributes['password']);
        }

        $user->update($attributes);

        if ($hasClassroom && $user->role === UserRole::STUDENT) {
            $this->syncClassroom($user, $classroomId);
        }

        return $user;
    }

    /**
     * Sincroniza a turma do estudante (dentro da sua instituição).
     */
    private function syncClassroom(User $user, mixed $classroomId): void
    {
        if ($classroomId === null) {
            $user->enrolledClassrooms()->sync([]);

            return;
        }

        $isSameInstitution = Classroom::query()
            ->whereKey($classroomId)
            ->where('institution_id', $user->institution_id)
            ->exists();

        if ($isSameInstitution) {
            $user->enrolledClassrooms()->sync([(int) $classroomId]);
        }
    }
}
