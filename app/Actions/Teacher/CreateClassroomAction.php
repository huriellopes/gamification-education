<?php

declare(strict_types=1);

namespace App\Actions\Teacher;

use App\Enums\GeneralStatus;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Support\Str;

class CreateClassroomAction
{
    /**
     * Cria uma turma para o professor na instituição ativa dele. A turma nasce
     * INATIVA e PENDENTE (approved_at nulo) — só entra em atividade após um
     * admin aprovar. O professor já pode matricular alunos enquanto pendente.
     *
     * @param  array{name: string, description?: string|null}  $attributes
     */
    public function __invoke(array $attributes, User $teacher): Classroom
    {
        return Classroom::create([
            'institution_id' => $teacher->institution_id,
            'teacher_id' => $teacher->id,
            'name' => $attributes['name'],
            'slug' => Str::slug($attributes['name']) . '-' . mb_strtolower(Str::random(6)),
            'description' => $attributes['description'] ?? null,
            'is_active' => GeneralStatus::INACTIVE,
            'approved_at' => null,
        ]);
    }
}
