<?php

declare(strict_types=1);

namespace App\Actions\SuperAdmin\User;

use App\Models\Classroom;
use App\Models\User;
use App\Services\Mail\InstitutionUserMailService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateUserAction
{
    public function __construct(
        private readonly InstitutionUserMailService $mailService,
    ) {}

    /**
     * Cria um usuário (admin/professor/aluno) a partir dos dados validados,
     * sincronizando instituições e (para alunos) a turma, e enviando boas-vindas.
     *
     * @param  array<string, mixed>  $validated
     */
    public function __invoke(array $validated): User
    {
        $role = $validated['role'];
        $institutionIds = $validated['institution_ids'] ?? [];
        $classroomId = $validated['classroom_id'] ?? null;

        $attributes = $validated;
        unset($attributes['institution_ids'], $attributes['classroom_id']);

        $tempPassword = $attributes['password'] ?? null;

        if (empty($tempPassword)) {
            $tempPassword = Str::random(12);
        }

        $attributes['password'] = bcrypt($tempPassword);
        $attributes['must_change_password'] = true;

        // Admins e professores podem ser vinculados a várias instituições.
        $multiInstitution = in_array($role, ['admin', 'teacher'], true);

        // A primeira instituição selecionada é o contexto ativo inicial.
        if ($multiInstitution && $institutionIds === [] && !empty($validated['institution_id'])) {
            $institutionIds = [$validated['institution_id']];
        }

        if ($multiInstitution && $institutionIds !== []) {
            $attributes['institution_id'] = $institutionIds[0];
        }

        $user = DB::transaction(function () use ($attributes, $role, $multiInstitution, $institutionIds, $classroomId) {
            $user = User::create($attributes);

            if ($multiInstitution && $institutionIds !== []) {
                $user->institutions()->sync($institutionIds);
            } elseif (!empty($user->institution_id)) {
                $user->institutions()->sync([$user->institution_id]);
            }

            if ($role === 'student' && $classroomId !== null) {
                $belongs = Classroom::query()
                    ->whereKey($classroomId)
                    ->where('institution_id', $user->institution_id)
                    ->exists();

                if ($belongs) {
                    $user->enrolledClassrooms()->syncWithoutDetaching([$classroomId]);
                }
            }

            return $user;
        });

        $this->mailService->sendWelcome($user, (string) $tempPassword);

        return $user;
    }
}
