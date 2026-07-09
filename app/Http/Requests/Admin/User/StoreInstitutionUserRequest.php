<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInstitutionUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var User|null $user */
        $user = $this->user();

        $allowedRoles = $user?->isSuperAdmin()
            ? 'admin,teacher,student'
            : 'teacher,student';

        $role = $this->input('role');

        // Professores e admins podem ser vinculados a várias instituições.
        // Para professor é opcional (fallback: instituição atual do admin);
        // um admin de instituição só pode escolher entre as que ele gerencia.
        $managedIds = $user?->managedInstitutionIds() ?? [];
        $isSuperAdmin = $user?->isSuperAdmin() ?? false;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'string', 'in:' . $allowedRoles],
            'classroom_id' => ['nullable', 'exists:classrooms,id'],
            'institution_id' => [
                $user?->isSuperAdmin() && $role !== 'admin' ? 'required' : 'nullable',
                'exists:institutions,id',
            ],
            'institution_ids' => [
                $role === 'admin' && !$this->filled('institution_id') ? 'required' : 'nullable',
                'array',
            ],
            'institution_ids.*' => array_values(array_filter([
                'integer',
                'exists:institutions,id',
                $isSuperAdmin ? null : Rule::in($managedIds),
            ])),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'institution_ids.required' => 'Selecione ao menos uma instituição.',
            'institution_ids.*.in' => 'Você só pode vincular às instituições que administra.',
        ];
    }
}
