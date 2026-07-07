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

        // Professores podem lecionar em várias instituições (pivot). A primeira
        // selecionada passa a ser a instituição principal.
        $institutionIds = $attributes['institution_ids'] ?? null;
        unset($attributes['institution_ids']);

        if (empty($attributes['password'])) {
            unset($attributes['password']);
        } else {
            $attributes['password'] = bcrypt($attributes['password']);
        }

        if ($user->role === UserRole::TEACHER && is_array($institutionIds) && $institutionIds !== []) {
            $institutionIds = array_values(array_map('intval', $institutionIds));
            $attributes['institution_id'] = $institutionIds[0];
        }

        $user->update($attributes);

        if ($user->role === UserRole::TEACHER && is_array($institutionIds) && $institutionIds !== []) {
            $user->institutions()->sync($institutionIds);
        }

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
