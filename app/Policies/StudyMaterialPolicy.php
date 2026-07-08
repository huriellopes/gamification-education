<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\StudyMaterial;
use App\Models\Subject;
use App\Models\User;
use App\Policies\Concerns\ScopesToInstitution;

class StudyMaterialPolicy
{
    use ScopesToInstitution;

    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, StudyMaterial $studyMaterial): bool
    {
        return $user->can('view', $studyMaterial->subject);
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
    public function update(User $user, StudyMaterial $studyMaterial): bool
    {
        return $user->can('manageContent', $studyMaterial->subject);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, StudyMaterial $studyMaterial): bool
    {
        return $this->update($user, $studyMaterial);
    }

    /**
     * Determine whether the user can complete the study material.
     */
    public function complete(User $user, StudyMaterial $studyMaterial): bool
    {
        /** @var Subject|null $subject */
        $subject = $studyMaterial->subject;

        return $user->isStudent() && $subject && $this->sharesInstitution($user, $subject->institution_id);
    }

    public function restore(User $user, StudyMaterial $studyMaterial): bool
    {
        return $this->update($user, $studyMaterial);
    }

    public function forceDelete(User $user, StudyMaterial $studyMaterial): bool
    {
        /** @var Subject|null $subject */
        $subject = $studyMaterial->subject;

        return $user->isSuperAdmin() || ($user->isInstitutionAdmin() && $subject && $this->sharesInstitution($user, $subject->institution_id));
    }
}
