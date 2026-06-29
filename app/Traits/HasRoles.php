<?php

declare(strict_types=1);

namespace App\Traits;

use App\Enums\GeneralStatus;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;

/**
 * Role-based helpers and query scopes for the User model.
 *
 * Keeps role logic cohesive and out of the model body so it stays lean.
 */
trait HasRoles
{
    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SUPER_ADMIN;
    }

    public function isInstitutionAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    public function isTeacher(): bool
    {
        return $this->role === UserRole::TEACHER;
    }

    public function isStudent(): bool
    {
        return $this->role === UserRole::STUDENT;
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeRole(Builder $query, UserRole $role): Builder
    {
        return $query->where('role', $role);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeStudents(Builder $query): Builder
    {
        return $query->where('role', UserRole::STUDENT);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeTeachers(Builder $query): Builder
    {
        return $query->where('role', UserRole::TEACHER);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', GeneralStatus::ACTIVE);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeForInstitution(Builder $query, int $institutionId): Builder
    {
        return $query->where('institution_id', $institutionId);
    }
}
