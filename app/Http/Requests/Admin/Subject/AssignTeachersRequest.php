<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Subject;

use Illuminate\Foundation\Http\FormRequest;

class AssignTeachersRequest extends FormRequest
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
        return [
            'teacher_ids' => ['required', 'array'],
            'teacher_ids.*' => ['exists:users,id'],
        ];
    }
}
