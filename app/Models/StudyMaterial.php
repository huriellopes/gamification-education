<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['subject_id', 'title', 'content', 'points_reward'])]
class StudyMaterial extends Model
{
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'study_material_user')
                    ->withPivot('completed_at')
                    ->withTimestamps();
    }
}
