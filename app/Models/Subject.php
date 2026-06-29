<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\GeneralStatus;
use App\Enums\UserRole;
use App\Traits\Activatable;
use App\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\DeletedModels\Models\Concerns\KeepsDeletedModels;

/**
 * @property string $slug
 * @property string $duration
 * @property GeneralStatus $is_active
 */
#[Fillable(['institution_id', 'classroom_id', 'name', 'slug', 'description', 'duration', 'is_active'])]
class Subject extends Model
{
    use Activatable, BelongsToInstitution, KeepsDeletedModels;

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * @return BelongsTo<Classroom, $this>
     */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * @return HasMany<StudyMaterial, $this>
     */
    public function studyMaterials(): HasMany
    {
        return $this->hasMany(StudyMaterial::class);
    }

    /**
     * @return HasMany<Test, $this>
     */
    public function tests(): HasMany
    {
        return $this->hasMany(Test::class);
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'subject_user')
            ->where('users.role', UserRole::TEACHER)
            ->withTimestamps();
    }

    public static function activeCount(): int
    {
        return self::active()->count();
    }

    protected function casts(): array
    {
        return [
            'is_active' => GeneralStatus::class,
        ];
    }
}
