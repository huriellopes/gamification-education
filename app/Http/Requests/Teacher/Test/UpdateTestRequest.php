<?php

declare(strict_types=1);

namespace App\Http\Requests\Teacher\Test;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTestRequest extends FormRequest
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
            'description' => ['required', 'string'],
            'points_reward' => ['required', 'integer', 'min:1', 'max:10000'],
        ];
    }
}
