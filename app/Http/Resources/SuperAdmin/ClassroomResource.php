<?php

declare(strict_types=1);

namespace App\Http\Resources\SuperAdmin;

use App\Models\Classroom;
use App\Models\Institution;
use App\Models\Subject;
use App\Models\User;
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
            'teacher' => $this->whenLoaded('teacher', function (): ?array {
                /** @var User|null $teacher */
                $teacher = $this->teacher;

                return $teacher === null ? null : [
                    'id' => $teacher->id,
                    'name' => $teacher->name,
                ];
            }),
            'institution' => $this->whenLoaded('institution', function (): ?array {
                /** @var Institution|null $institution */
                $institution = $this->institution;

                return $institution === null ? null : [
                    'id' => $institution->id,
                    'name' => $institution->name,
                ];
            }),
            'subjects' => $this->whenLoaded('subjects', function () {
                /** @var Collection<int, Subject> $subjects */
                $subjects = $this->subjects;

                return $subjects->map(fn (Subject $subject): array => [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'classroom_id' => $subject->classroom_id,
                ])->all();
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
