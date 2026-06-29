<?php

declare(strict_types=1);

namespace App\Actions\SuperAdmin\User;

use App\Models\Classroom;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateUserAction
{
    /**
     * Atualiza um usuário a partir dos dados validados, sincronizando
     * instituições e (para alunos) a turma.
     *
     * @param  array<string, mixed>  $validated
     */
    public function __invoke(User $user, array $validated): User
    {
        $role = $validated['role'];
        $institutionIds = $validated['institution_ids'] ?? [];
        $classroomId = $validated['classroom_id'] ?? null;

        $attributes = $validated;
        unset($attributes['institution_ids'], $attributes['classroom_id']);

        if (empty($attributes['password'])) {
            unset($attributes['password']);
        } else {
            $attributes['password'] = bcrypt($attributes['password']);
        }

        if ($role === 'admin' && $institutionIds === [] && !empty($validated['institution_id'])) {
            $institutionIds = [$validated['institution_id']];
        }

        if ($role === 'admin' && $institutionIds !== [] && !in_array($user->institution_id, $institutionIds, true)) {
            $attributes['institution_id'] = $institutionIds[0];
        }

        DB::transaction(function () use ($user, $attributes, $role, $institutionIds, $classroomId) {
            $user->update($attributes);

            if ($role === 'admin' && $institutionIds !== []) {
                $user->institutions()->sync($institutionIds);
            } elseif (!empty($user->institution_id)) {
                $user->institutions()->sync([$user->institution_id]);
            }

            if ($user->isStudent()) {
                $isSameInstitution = $classroomId !== null && Classroom::query()
                    ->whereKey($classroomId)
                    ->where('institution_id', $user->institution_id)
                    ->exists();

                $user->enrolledClassrooms()->sync($isSameInstitution ? [$classroomId] : []);
            }
        });

        return $user;
    }
}
