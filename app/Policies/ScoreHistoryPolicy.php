<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ScoreHistory;
use App\Models\User;

class ScoreHistoryPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ScoreHistory $scoreHistory): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->id === $scoreHistory->user_id) {
            return true;
        }

        if ($user->isInstitutionAdmin()) {
            /** @var User|null $historyUser */
            $historyUser = $scoreHistory->user;

            return $historyUser instanceof User && $user->canManageInstitutionUser($historyUser);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, ScoreHistory $scoreHistory): bool
    {
        return false;
    }

    public function delete(User $user, ScoreHistory $scoreHistory): bool
    {
        return false;
    }

    public function restore(User $user, ScoreHistory $scoreHistory): bool
    {
        return false;
    }

    public function forceDelete(User $user, ScoreHistory $scoreHistory): bool
    {
        return false;
    }
}
