<?php

declare(strict_types=1);

namespace App\Http\Requests\Teacher\Content;

use Illuminate\Foundation\Http\FormRequest;

class GenerateContentRequest extends FormRequest
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
            'theme' => ['required', 'string', 'max:255'],
        ];
    }
}
