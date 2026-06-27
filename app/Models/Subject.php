<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['institution_id', 'name', 'description'])]
class Subject extends Model
{
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function studyMaterials()
    {
        return $this->hasMany(StudyMaterial::class);
    }

    public function tests()
    {
        return $this->hasMany(Test::class);
    }
}
