<?php

declare(strict_types=1);

namespace App\Http\Requests\SuperAdmin\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
        $role = $this->input('role');

        // Admins e professores podem ser vinculados a várias instituições;
        // alunos usam uma única instituição.
        $multiInstitution = in_array($role, ['admin', 'teacher'], true);

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', 'in:admin,teacher,student'],
            'institution_id' => [
                $role === 'student' ? 'required' : 'nullable',
                'exists:institutions,id',
            ],
            'institution_ids' => [
                $multiInstitution && !$this->filled('institution_id') ? 'required' : 'nullable',
                'array',
            ],
            'institution_ids.*' => ['exists:institutions,id'],
            'classroom_id' => ['nullable', 'exists:classrooms,id'],
        ];
    }
}
