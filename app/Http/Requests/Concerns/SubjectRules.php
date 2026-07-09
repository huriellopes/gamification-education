<?php

declare(strict_types=1);

namespace App\Http\Requests\Concerns;

use App\Models\User;
use Illuminate\Validation\Rule;

/**
 * Regras de validação compartilhadas dos formulários de matéria (Store/Update),
 * evitando a duplicação das mesmas regras entre Super Admin, Admin e Professor.
 * A autorização (`authorize()`) permanece específica de cada request.
 */
trait SubjectRules
{
    /**
     * Campos comuns a qualquer papel. `institution_id` é obrigatório apenas
     * para o super admin (que escolhe a instituição); para os demais a
     * instituição vem do contexto e o campo é opcional.
     *
     * @return array<string, mixed>
     */
    protected function subjectRules(): array
    {
        /** @var User|null $user */
        $user = $this->user();

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration' => ['required', 'integer', 'min:1', 'max:9999'],
            'institution_id' => [
                $user?->isSuperAdmin() ? 'required' : 'nullable',
                'exists:institutions,id',
            ],
        ];
    }

    /**
     * Vínculo opcional com uma turma, restrito às turmas do próprio professor.
     *
     * @return array<string, mixed>
     */
    protected function teacherClassroomRule(): array
    {
        /** @var User|null $user */
        $user = $this->user();

        return [
            'classroom_id' => [
                'nullable',
                Rule::exists('classrooms', 'id')->where(
                    fn ($query) => $query->where('teacher_id', $user?->id),
                ),
            ],
        ];
    }
}
