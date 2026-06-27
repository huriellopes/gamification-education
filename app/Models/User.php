<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'institution_id', 'role', 'points'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
        ];
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function testAttempts()
    {
        return $this->hasMany(TestAttempt::class);
    }

    public function completedMaterials()
    {
        return $this->belongsToMany(StudyMaterial::class, 'study_material_user')
                    ->withPivot('completed_at')
                    ->withTimestamps();
    }

    public function scoreHistories()
    {
        return $this->hasMany(ScoreHistory::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
