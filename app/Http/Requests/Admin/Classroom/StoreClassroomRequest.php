<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Classroom;

use App\Http\Requests\Concerns\ClassroomRules;
use App\Models\Classroom;
use Illuminate\Foundation\Http\FormRequest;

class StoreClassroomRequest extends FormRequest
{
    use ClassroomRules;

    public function authorize(): bool
    {
        return $this->user()?->can('create', Classroom::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->classroomRules();
    }
}
