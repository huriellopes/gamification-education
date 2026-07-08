<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Subject;
use App\Models\Test;
use App\Models\User;
use App\Policies\Concerns\ScopesToInstitution;

class TestPolicy
{
    use ScopesToInstitution;

    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Test $test): bool
    {
        return $user->can('view', $test->subject);
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
    public function update(User $user, Test $test): bool
    {
        return $user->can('manageContent', $test->subject);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Test $test): bool
    {
        return $this->update($user, $test);
    }

    /**
     * Determine whether the user can submit an attempt for the test.
     */
    public function submit(User $user, Test $test): bool
    {
        /** @var Subject|null $subject */
        $subject = $test->subject;

        return $user->isStudent() && $subject && $this->sharesInstitution($user, $subject->institution_id);
    }

    public function restore(User $user, Test $test): bool
    {
        return $this->update($user, $test);
    }

    public function forceDelete(User $user, Test $test): bool
    {
        /** @var Subject|null $subject */
        $subject = $test->subject;

        return $user->isSuperAdmin() || ($user->isInstitutionAdmin() && $subject && $this->sharesInstitution($user, $subject->institution_id));
    }
}
