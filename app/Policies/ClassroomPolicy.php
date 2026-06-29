<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Classroom;
use App\Models\User;

class ClassroomPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isInstitutionAdmin() || $user->isTeacher();
    }

    public function view(User $user, Classroom $classroom): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isTeacher()) {
            return $classroom->teacher_id === $user->id;
        }

        return $user->isInstitutionAdmin() && $classroom->institution_id === $user->institution_id;
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isInstitutionAdmin();
    }

    public function update(User $user, Classroom $classroom): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isInstitutionAdmin() && $classroom->institution_id === $user->institution_id;
    }

    public function delete(User $user, Classroom $classroom): bool
    {
        return $this->update($user, $classroom);
    }
}
