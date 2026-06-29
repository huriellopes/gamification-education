<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Subject;
use App\Models\Test;
use App\Models\TestAttempt;
use App\Models\User;

class TestAttemptPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, TestAttempt $testAttempt): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->id === $testAttempt->user_id) {
            return true;
        }

        return $user->can('view', $testAttempt->test);
    }

    public function create(User $user): bool
    {
        return $user->isStudent();
    }

    public function update(User $user, TestAttempt $testAttempt): bool
    {
        return false;
    }

    public function delete(User $user, TestAttempt $testAttempt): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        /** @var Test|null $test */
        $test = $testAttempt->test;
        /** @var Subject|null $subject */
        $subject = $test ? $test->subject : null;

        return $user->isInstitutionAdmin() && $subject && $subject->institution_id === $user->institution_id;
    }

    public function restore(User $user, TestAttempt $testAttempt): bool
    {
        return $this->delete($user, $testAttempt);
    }

    public function forceDelete(User $user, TestAttempt $testAttempt): bool
    {
        return $user->isSuperAdmin();
    }
}
