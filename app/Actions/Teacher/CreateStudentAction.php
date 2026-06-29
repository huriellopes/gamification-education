<?php

declare(strict_types=1);

namespace App\Actions\Teacher;

use App\Enums\GeneralStatus;
use App\Enums\UserRole;
use App\Models\Classroom;
use App\Models\User;
use App\Services\InstitutionUserMailService;
use Illuminate\Support\Str;

class CreateStudentAction
{
    public function __construct(
        private readonly InstitutionUserMailService $mailService,
    ) {}

    /**
     * Cadastra um novo estudante na instituição do professor e envia as boas-vindas.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function __invoke(array $attributes, User $teacher): User
    {
        // O vínculo com a turma não é coluna da tabela users; tratamos via pivot.
        $classroomId = $attributes['classroom_id'] ?? null;
        unset($attributes['classroom_id']);

        $attributes['role'] = UserRole::STUDENT->value;
        $attributes['institution_id'] = $teacher->institution_id;
        $attributes['is_active'] = GeneralStatus::ACTIVE;

        $password = $attributes['password'] ?? null;

        if (empty($password)) {
            $tempPassword = Str::random(12);
            $attributes['password'] = bcrypt($tempPassword);
            $attributes['must_change_password'] = true;
        } else {
            $tempPassword = (string) $password;
            $attributes['password'] = bcrypt($tempPassword);
            $attributes['must_change_password'] = true;
        }

        $student = User::create($attributes);

        $this->enrollInClassroom($student, $classroomId, $teacher);

        $this->mailService->sendWelcome($student, $tempPassword);

        return $student;
    }

    /**
     * Vincula o estudante a uma turma do próprio professor (mesma instituição).
     */
    private function enrollInClassroom(User $student, mixed $classroomId, User $teacher): void
    {
        if ($classroomId === null) {
            return;
        }

        $belongsToTeacher = Classroom::query()
            ->whereKey($classroomId)
            ->where('teacher_id', $teacher->id)
            ->exists();

        if ($belongsToTeacher) {
            $student->enrolledClassrooms()->syncWithoutDetaching([(int) $classroomId]);
        }
    }
}
