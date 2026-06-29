<?php

declare(strict_types=1);

namespace App\Http\Requests\Teacher\Question;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionRequest extends FormRequest
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
            'question_text' => ['required', 'string', 'max:1000'],
            'options' => ['required', 'array', 'min:2', 'max:8'],
            'options.*' => ['required', 'string', 'max:255'],
            'correct_option_index' => [
                'required',
                'integer',
                'min:0',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $options = $this->input('options');

                    if (is_array($options) && (int) $value > count($options) - 1) {
                        $fail(__('validation.correct_option_out_of_range'));
                    }
                },
            ],
        ];
    }

    /**
     * Normaliza as opções (remove espaços em branco) antes da validação.
     */
    protected function prepareForValidation(): void
    {
        $options = $this->input('options');

        if (is_array($options)) {
            $this->merge([
                'options' => array_map(
                    fn ($option) => is_string($option) ? mb_trim($option) : $option,
                    $options,
                ),
            ]);
        }
    }
}
