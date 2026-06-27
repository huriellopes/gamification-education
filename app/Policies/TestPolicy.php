<?php

namespace App\Policies;

use App\Models\Subject;
use App\Models\Test;
use App\Models\User;

class TestPolicy
{
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

        return $user->isStudent() && $subject && $subject->institution_id === $user->institution_id;
    }
}
