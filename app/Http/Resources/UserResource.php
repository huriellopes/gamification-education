<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Institution;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'institution_id' => $this->institution_id,
            'institution' => $this->whenLoaded('institution', function () {
                /** @var Institution $institution */
                $institution = $this->institution;

                return [
                    'id' => $institution->id,
                    'name' => $institution->name,
                ];
            }),
            'points' => $this->points,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
