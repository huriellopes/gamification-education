<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isInstitutionAdmin();
    }

    public function view(User $user, User $model): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isInstitutionAdmin()) {
            return $user->institution_id === $model->institution_id;
        }

        return $user->id === $model->id;
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isInstitutionAdmin();
    }

    public function update(User $user, User $model): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isInstitutionAdmin()) {
            return $user->institution_id === $model->institution_id;
        }

        return $user->id === $model->id;
    }

    public function delete(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isInstitutionAdmin() && $user->institution_id === $model->institution_id && !$model->isSuperAdmin();
    }

    public function restore(User $user, User $model): bool
    {
        return $user->isSuperAdmin() || ($user->isInstitutionAdmin() && $user->institution_id === $model->institution_id && !$model->isSuperAdmin());
    }

    public function forceDelete(User $user, User $model): bool
    {
        return $user->isSuperAdmin();
    }

    public function impersonate(User $user, User $model): bool
    {
        return $user->isSuperAdmin() && $user->id !== $model->id;
    }
}
