<?php

declare(strict_types=1);

namespace App\Http\Requests\Teacher\Classroom;

use Illuminate\Foundation\Http\FormRequest;

class EnrollStudentsRequest extends FormRequest
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
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', 'exists:users,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'student_ids.required' => 'Selecione ao menos um aluno.',
            'student_ids.min' => 'Selecione ao menos um aluno.',
        ];
    }
}
