<?php

declare(strict_types=1);

namespace App\Http\Resources\Ranking;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 *
 * @property int $position
 */
class RankingUserResource extends JsonResource
{
    public function __construct(User $resource, private readonly int $position)
    {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'position' => $this->position,
            'id' => $this->id,
            'name' => $this->name,
            'points' => $this->points,
            'institution' => $this->institution ? $this->institution->name : 'N/A',
        ];
    }
}
