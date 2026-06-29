<?php

declare(strict_types=1);

namespace App\Http\Resources\Ranking;

use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Subject
 */
class RankingSubjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * Preserva a forma serializada completa do modelo para manter a
     * compatibilidade com o filtro de matérias da página de ranking.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Subject $subject */
        $subject = $this->resource;

        return $subject->toArray();
    }
}
