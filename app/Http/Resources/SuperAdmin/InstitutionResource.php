<?php

declare(strict_types=1);

namespace App\Http\Resources\SuperAdmin;

use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Institution
 */
class InstitutionResource extends JsonResource
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
            'razao_social' => $this->razao_social,
            'cnpj' => $this->cnpj,
            'slug' => $this->slug,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'address' => $this->address,
            'phones' => $this->phones,
            'subjects_count' => $this->subjects_count,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
