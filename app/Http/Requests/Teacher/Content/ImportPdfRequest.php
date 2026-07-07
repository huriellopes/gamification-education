<?php

declare(strict_types=1);

namespace App\Http\Requests\Teacher\Content;

use Illuminate\Foundation\Http\FormRequest;

class ImportPdfRequest extends FormRequest
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
            'file' => [
                'required',
                'file',
                'mimetypes:application/pdf,application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'mimes:pdf,pptx',
                'max:20480',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'Selecione um arquivo PDF ou PowerPoint (.pptx).',
            'file.mimetypes' => 'O arquivo deve ser um PDF ou PowerPoint (.pptx).',
            'file.mimes' => 'O arquivo deve ser um PDF ou PowerPoint (.pptx).',
            'file.max' => 'O arquivo deve ter no máximo 20 MB.',
        ];
    }
}
