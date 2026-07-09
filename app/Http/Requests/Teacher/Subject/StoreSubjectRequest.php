<?php

declare(strict_types=1);

namespace App\Http\Requests\Teacher\Subject;

use App\Http\Requests\Concerns\SubjectRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreSubjectRequest extends FormRequest
{
    use SubjectRules;

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
            ...$this->subjectRules(),
            ...$this->teacherClassroomRule(),
        ];
    }
}
