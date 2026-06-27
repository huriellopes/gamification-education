<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'student';
    }

    public function rules(): array
    {
        return [
            'answers' => ['required', 'array'],
        ];
    }
}
