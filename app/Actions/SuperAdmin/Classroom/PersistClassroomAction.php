<?php

declare(strict_types=1);

namespace App\Actions\SuperAdmin\Classroom;

use App\Enums\GeneralStatus;
use App\Enums\UserRole;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PersistClassroomAction
{
    /**
     * Persiste (cria ou atualiza) uma turma e sincroniza suas matérias.
     *
     * @param  array{name: string, description: string|null, teacher_id: int|null, subject_ids?: array<int, int>|null}  $data
     */
    public function __invoke(Classroom $classroom, array $data, int $institutionId): Classroom
    {
        $teacherId = $this->resolveTeacherId($data['teacher_id'] ?? null, $institutionId);

        return DB::transaction(function () use ($classroom, $data, $institutionId, $teacherId): Classroom {
            $classroom->fill([
                'institution_id' => $institutionId,
                'teacher_id' => $teacherId,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
            ]);

            if (!$classroom->exists) {
                $classroom->is_active = GeneralStatus::ACTIVE;
            }

            $classroom->save();

            $this->syncSubjects($classroom, $data['subject_ids'] ?? [], $institutionId);

            return $classroom;
        });
    }

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
