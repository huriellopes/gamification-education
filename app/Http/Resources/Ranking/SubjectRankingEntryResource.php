<?php

declare(strict_types=1);

namespace App\Http\Resources\Ranking;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubjectRankingEntryResource extends JsonResource
{
    /**
     * @param  object  $resource
     */
    public function __construct($resource, private readonly int $position)
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
        /** @var object{user_name: string, total_subject_score: mixed, institution_name?: string|null} $entry */
        $entry = $this->resource;

        return [
            'position' => $this->position,
            'name' => $entry->user_name,
            'points' => $entry->total_subject_score,
            'institution' => $entry->institution_name ?? 'N/A',
        ];
    }
}
