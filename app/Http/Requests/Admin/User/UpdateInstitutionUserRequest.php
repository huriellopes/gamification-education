<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInstitutionUserRequest extends FormRequest
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
        /** @var User|null $authUser */
        $authUser = $this->user();

        /** @var User|null $routeUser */
        $routeUser = $this->route('user');
        $userId = $routeUser instanceof User ? $routeUser->id : $routeUser;

        $allowedRoles = $authUser?->isSuperAdmin()
            ? 'admin,teacher,student'
            : 'teacher,student';

        $role = $this->input('role');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . ($userId ?? '')],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'string', 'in:' . $allowedRoles],
            'classroom_id' => ['nullable', 'exists:classrooms,id'],
            'institution_id' => [
                $authUser?->isSuperAdmin() && $role !== 'admin' ? 'required' : 'nullable',
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
