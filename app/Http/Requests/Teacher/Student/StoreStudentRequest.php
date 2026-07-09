<?php

declare(strict_types=1);

namespace App\Http\Requests\Teacher\Student;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
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
                $role === 'admin' && !$this->has('institution_id') ? 'required' : 'nullable',
                'array',
            ],
            'institution_ids.*' => ['exists:institutions,id'],
        ];
    }
}
