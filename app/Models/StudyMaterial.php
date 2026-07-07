<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\DeletedModels\Models\Concerns\KeepsDeletedModels;

#[Fillable(['subject_id', 'title', 'content', 'points_reward', 'source_hash'])]
class StudyMaterial extends Model
{
    use KeepsDeletedModels;

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'study_material_user')
            ->withPivot('completed_at')
            ->withTimestamps();
    }
}
