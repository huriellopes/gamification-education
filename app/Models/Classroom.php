<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\GeneralStatus;
use App\Traits\Activatable;
use App\Traits\BelongsToInstitution;
use Database\Factories\ClassroomFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\DeletedModels\Models\Concerns\KeepsDeletedModels;

/**
 * @property GeneralStatus $is_active
 * @property Carbon|null $approved_at
 * @property int|null $approved_by
 */
#[Fillable(['institution_id', 'teacher_id', 'name', 'slug', 'description', 'is_active', 'approved_at', 'approved_by'])]
class Classroom extends Model implements AuditableContract
{
    /** @use HasFactory<ClassroomFactory> */
    use Activatable, Auditable, BelongsToInstitution, HasFactory, KeepsDeletedModels;

    /**
     * @return BelongsTo<Institution, $this>
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * The single teacher responsible for the classroom.
     *
     * @return BelongsTo<User, $this>
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * @return HasMany<Subject, $this>
     */
    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    /**
     * Students enrolled in the classroom.
     *
     * @return BelongsToMany<User, $this>
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'classroom_student')
            ->withTimestamps();
    }

    /**
     * O professor responsável (teacher_id) — turma "pendente" enquanto não
     * aprovada por um admin.
     *
     * @return BelongsTo<User, $this>
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Turma aprovada por um admin (turmas criadas por admin já nascem aprovadas).
     */
    public function isApproved(): bool
    {
        return $this->approved_at !== null;
    }

    /**
     * @param  Builder<Classroom>  $query
     * @return Builder<Classroom>
     */
    protected function scopePending(Builder $query): Builder
    {
        return $query->whereNull('approved_at');
    }

    protected function casts(): array
    {
        return [
            'is_active' => GeneralStatus::class,
            'approved_at' => 'datetime',
        ];
    }
}
