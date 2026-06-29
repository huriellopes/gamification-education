<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Enums\GeneralStatus;
use App\Enums\UserRole;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PersistClassroomAction
{
    /**
     * Persiste uma turma e sincroniza professor + matérias, restrito a uma instituição.
     *
     * @param  array{name: string, description?: string|null, teacher_id?: int|null, subject_ids?: array<int, int>|null}  $attributes
     */
    public function __invoke(Classroom $classroom, array $attributes, int $institutionId): Classroom
    {
        $teacherId = $this->resolveTeacherId(
            isset($attributes['teacher_id']) ? $attributes['teacher_id'] : null,
            $institutionId,
        );

        DB::transaction(function () use ($classroom, $attributes, $institutionId, $teacherId): void {
            $classroom->fill([
                'institution_id' => $institutionId,
                'teacher_id' => $teacherId,
                'name' => $attributes['name'],
                'description' => $attributes['description'] ?? null,
            ]);

            if (!$classroom->exists) {
                $classroom->is_active = GeneralStatus::ACTIVE;
            }

            $classroom->save();

            $this->syncSubjects($classroom, $attributes['subject_ids'] ?? [], $institutionId);
        });

        return $classroom;
    }

    /**
     * Only allow teachers that belong to the same institution.
     */
    private function resolveTeacherId(?int $teacherId, int $institutionId): ?int
    {
        if ($teacherId === null) {
            return null;
        }

        $isValid = User::query()
            ->whereKey($teacherId)
            ->where('role', UserRole::TEACHER)
            ->where('institution_id', $institutionId)
            ->exists();

        return $isValid ? $teacherId : null;
    }

    /**
     * Attach the selected subjects (of this institution) to the classroom and detach the rest.
     *
     * @param  array<int, int>  $subjectIds
     */
    private function syncSubjects(Classroom $classroom, array $subjectIds, int $institutionId): void
    {
        Subject::query()
            ->where('classroom_id', $classroom->id)
            ->update(['classroom_id' => null]);

        if ($subjectIds !== []) {
            Subject::query()
                ->whereIn('id', $subjectIds)
                ->where('institution_id', $institutionId)
                ->update(['classroom_id' => $classroom->id]);
        }
    }
}
