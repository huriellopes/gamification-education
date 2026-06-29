<?php

declare(strict_types=1);

namespace App\Http\Resources\Student;

use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Subject
 */
class SubjectResource extends JsonResource
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
            'classroom_id' => $this->classroom_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'duration' => $this->duration,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
