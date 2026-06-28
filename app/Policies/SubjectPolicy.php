<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Subject;
use App\Models\User;

class SubjectPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Subject $subject): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($subject->institution_id !== $user->institution_id) {
            return false;
        }

        if ($user->isTeacher()) {
            return $subject->teachers()->where('user_id', $user->id)->exists();
        }

        return $user->isInstitutionAdmin() || $user->isStudent();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isInstitutionAdmin() || $user->isTeacher();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Subject $subject): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($subject->institution_id !== $user->institution_id) {
            return false;
        }

        if ($user->isInstitutionAdmin()) {
            return true;
        }

        if ($user->isTeacher()) {
            return $subject->teachers()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Subject $subject): bool
    {
        return $this->update($user, $subject);
    }

    /**
     * Determine whether the user can manage content (study materials, tests) for the subject.
     */
    public function manageContent(User $user, Subject $subject): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($subject->institution_id !== $user->institution_id) {
            return false;
        }

        if ($user->isInstitutionAdmin()) {
            return true;
        }

        if ($user->isTeacher()) {
            return $subject->teachers()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    public function restore(User $user, Subject $subject): bool
    {
        return $this->update($user, $subject);
    }

    public function forceDelete(User $user, Subject $subject): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isInstitutionAdmin() && $subject->institution_id === $user->institution_id;
    }
}
