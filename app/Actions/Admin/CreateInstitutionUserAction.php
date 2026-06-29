<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Enums\GeneralStatus;
use App\Enums\UserRole;
use App\Models\Classroom;
use App\Models\User;
use App\Services\InstitutionUserMailService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateInstitutionUserAction
{
    public function __construct(
        private readonly InstitutionUserMailService $mailService,
    ) {}

    /**
     * Cadastra um novo membro (professor ou estudante) na instituição e envia as boas-vindas.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function __invoke(array $attributes, int $institutionId): User
    {
        $classroomId = $attributes['classroom_id'] ?? null;
        unset($attributes['classroom_id']);

        $attributes['institution_id'] = $institutionId;
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

        $user = DB::transaction(function () use ($attributes, $classroomId, $institutionId) {
            $user = User::create($attributes);

            $this->enrollInClassroom($user, $classroomId, $institutionId);

            return $user;
        });

        // E-mail fora da transação (mailable é ShouldQueue).
        $this->mailService->sendWelcome($user, $tempPassword);

        return $user;
    }

    /**
     * Vincula um estudante a uma turma da mesma instituição.
     */
    private function enrollInClassroom(User $user, mixed $classroomId, int $institutionId): void
    {
        if ($classroomId === null || $user->role !== UserRole::STUDENT) {
            return;
        }

        $isSameInstitution = Classroom::query()
            ->whereKey($classroomId)
            ->where('institution_id', $institutionId)
            ->exists();

        if ($isSameInstitution) {
            $user->enrolledClassrooms()->syncWithoutDetaching([(int) $classroomId]);
        }
    }
}
