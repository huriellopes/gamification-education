<?php

declare(strict_types=1);

namespace App\Http\Requests\SuperAdmin\Classroom;

use App\Http\Requests\Concerns\ClassroomRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateClassroomRequest extends FormRequest
{
    use ClassroomRules;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->classroomRules();
    }
}
