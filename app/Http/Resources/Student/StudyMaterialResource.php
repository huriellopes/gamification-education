<?php

declare(strict_types=1);

namespace App\Http\Resources\Student;

use App\Models\StudyMaterial;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin StudyMaterial
 */
class StudyMaterialResource extends JsonResource
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
            'subject_id' => $this->subject_id,
            'title' => $this->title,
            'content' => $this->content,
            'points_reward' => $this->points_reward,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
