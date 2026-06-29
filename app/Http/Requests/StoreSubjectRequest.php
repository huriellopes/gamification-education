<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && (
            $this->user()->isSuperAdmin() ||
            $this->user()->isInstitutionAdmin()
        );
    }

    public function rules(): array
    {
        return [
            'institution_id' => ['nullable', 'exists:institutions,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration' => ['required', 'string', 'max:255'],
        ];
    }
}
