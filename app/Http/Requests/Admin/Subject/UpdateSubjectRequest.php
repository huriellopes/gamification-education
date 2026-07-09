<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Subject;

use App\Http\Requests\Concerns\SubjectRules;
use App\Models\Subject;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSubjectRequest extends FormRequest
{
    use SubjectRules;

    public function authorize(): bool
    {
        /** @var Subject|null $subject */
        $subject = $this->route('subject');

        return $subject instanceof Subject && $this->user()?->can('update', $subject);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->subjectRules();
    }
}
