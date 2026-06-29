<?php

declare(strict_types=1);

namespace App\Http\Requests\Teacher\Subject;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreSubjectRequest extends FormRequest
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

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration' => ['required', 'string', 'max:255'],
            'institution_id' => [
                $user?->isSuperAdmin() ? 'required' : 'nullable',
                'exists:institutions,id',
            ],
        ];
    }
}
