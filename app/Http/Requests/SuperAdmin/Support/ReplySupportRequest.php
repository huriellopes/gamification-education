<?php

declare(strict_types=1);

namespace App\Http\Requests\SuperAdmin\Support;

use Illuminate\Foundation\Http\FormRequest;

class ReplySupportRequest extends FormRequest
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
            'reply' => ['required', 'string', 'max:5000'],
        ];
    }
}
