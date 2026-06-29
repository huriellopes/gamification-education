<?php

declare(strict_types=1);

namespace App\Http\Requests\Teacher\StudyMaterial;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudyMaterialRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'points_reward' => ['required', 'integer', 'min:1'],
        ];
    }
}
