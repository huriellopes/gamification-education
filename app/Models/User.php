<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\GeneralStatus;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\DeletedModels\Models\Concerns\KeepsDeletedModels;

/**
 * @property UserRole $role
 * @property GeneralStatus $is_active
 */
#[Fillable(['name', 'email', 'password', 'institution_id', 'role', 'points', 'is_active', 'must_change_password', 'last_login_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, KeepsDeletedModels, Notifiable;

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function testAttempts(): HasMany
    {
        return $this->hasMany(TestAttempt::class);
    }

    public function completedMaterials(): BelongsToMany
    {
        return $this->belongsToMany(StudyMaterial::class, 'study_material_user')
            ->withPivot('completed_at')
            ->withTimestamps();
    }

    public function scoreHistories(): HasMany
    {
        return $this->hasMany(ScoreHistory::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'subject_user')
            ->withTimestamps();
    }

    public function institutions(): BelongsToMany
    {
        return $this->belongsToMany(Institution::class, 'institution_user')
            ->withTimestamps();
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SUPER_ADMIN;
    }

    public function isInstitutionAdmin(): bool
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

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    public function scopeStudents($query)
    {
        return $query->where('role', UserRole::STUDENT);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', GeneralStatus::ACTIVE);
    }

    public static function activeStudentsCount(): int
    {
        return self::students()->active()->count();
    }

    public static function studentsTotalXp(): int|float|string
    {
        return self::students()->sum('points');
    }

    public static function getSuperAdmin(): ?self
    {
        return self::where('role', UserRole::SUPER_ADMIN)->first();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'points' => 'integer',
            'is_active' => GeneralStatus::class,
            'role' => UserRole::class,
            'must_change_password' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }
}
