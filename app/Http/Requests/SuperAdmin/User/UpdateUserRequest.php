<?php

declare(strict_types=1);

namespace App\Http\Requests\SuperAdmin\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
        $user = $this->route('user');
        $userId = $user instanceof User ? $user->id : $user;

        $role = $this->input('role');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . ($userId ?? '')],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'string', 'in:admin,teacher,student'],
            'institution_id' => [
                $role !== 'admin' ? 'required' : 'nullable',
                'exists:institutions,id',
            ],
            'institution_ids' => [
                $role === 'admin' && !$this->has('institution_id') ? 'required' : 'nullable',
                'array',
            ],
            'institution_ids.*' => ['exists:institutions,id'],
            'classroom_id' => ['nullable', 'exists:classrooms,id'],
        ];
    }
}
