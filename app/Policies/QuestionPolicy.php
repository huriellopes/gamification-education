<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Question;
use App\Models\User;

class QuestionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Question $question): bool
    {
        return $user->can('view', $question->test);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isInstitutionAdmin() || $user->isTeacher();
    }

    public function update(User $user, Question $question): bool
    {
        return $user->can('update', $question->test);
    }

    public function delete(User $user, Question $question): bool
    {
        return $this->update($user, $question);
    }

    public function restore(User $user, Question $question): bool
    {
        return $this->update($user, $question);
    }

    public function forceDelete(User $user, Question $question): bool
    {
        return $user->can('forceDelete', $question->test);
    }
}
