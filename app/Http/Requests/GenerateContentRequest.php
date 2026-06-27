<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && (
            $this->user()->isSuperAdmin() ||
            $this->user()->isInstitutionAdmin() ||
            $this->user()->isTeacher()
        );
    }

    public function rules(): array
    {
        return [
            'theme' => ['required', 'string', 'max:255'],
        ];
    }
}
