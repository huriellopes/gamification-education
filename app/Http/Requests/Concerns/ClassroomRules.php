<?php

declare(strict_types=1);

namespace App\Http\Requests\Concerns;

use App\Models\User;

/**
 * Regras de validação compartilhadas dos formulários de turma (Store/Update),
 * evitando a duplicação entre Super Admin e Admin. A autorização permanece
 * específica de cada request.
 */
trait ClassroomRules
{
    /**
     * `institution_id` é obrigatório apenas para o super admin; para o admin a
     * instituição vem do contexto ativo e o campo é opcional.
     *
     * @return array<string, mixed>
     */
    protected function classroomRules(): array
    {
        /** @var User|null $user */
        $user = $this->user();

        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'institution_id' => [
                $user?->isSuperAdmin() ? 'required' : 'nullable',
                'exists:institutions,id',
            ],
            'teacher_id' => ['nullable', 'exists:users,id'],
            'subject_ids' => ['nullable', 'array'],
            'subject_ids.*' => ['integer', 'exists:subjects,id'],
        ];
    }
}
