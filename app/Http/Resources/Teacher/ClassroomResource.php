<?php

declare(strict_types=1);

namespace App\Http\Resources\Teacher;

use App\Models\Classroom;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Classroom
 */
class ClassroomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'institution_id' => $this->institution_id,
            'teacher_id' => $this->teacher_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'subjects_count' => $this->subjects_count,
            'students_count' => $this->whenCounted('students'),
            'student_ids' => $this->whenLoaded('students', fn () => $this->students->pluck('id')->all()),
            'subjects' => $this->whenLoaded('subjects', function () {
                /** @var Collection<int, Subject> $subjects */
                $subjects = $this->subjects;

                return $subjects->map(fn (Subject $subject): array => [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'classroom_id' => $subject->classroom_id,
                    'slug' => $subject->slug,
                ])->all();
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
