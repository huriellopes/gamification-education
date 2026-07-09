<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Classroom;

use App\Http\Requests\Concerns\ClassroomRules;
use App\Models\Classroom;
use Illuminate\Foundation\Http\FormRequest;

class UpdateClassroomRequest extends FormRequest
{
    use ClassroomRules;

    public function authorize(): bool
    {
        /** @var Classroom|null $classroom */
        $classroom = $this->route('classroom');

        return $classroom instanceof Classroom && $this->user()?->can('update', $classroom);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->classroomRules();
    }
}
