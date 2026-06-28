<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\GeneralStatus;
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
#[Fillable(['institution_id', 'name', 'slug', 'description', 'duration', 'is_active'])]
class Subject extends Model
{
    use KeepsDeletedModels;

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function studyMaterials(): HasMany
    {
        return $this->hasMany(StudyMaterial::class);
    }

    public function tests(): HasMany
    {
        return $this->hasMany(Test::class);
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'subject_user')
            ->where('role', 'teacher')
            ->withTimestamps();
    }

    protected function casts(): array
    {
        return [
            'is_active' => GeneralStatus::class,
        ];
    }
}
