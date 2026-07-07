<?php

declare(strict_types=1);

namespace App\Actions\SuperAdmin\Subject;

use App\Actions\Subject\ResolveUniqueSlugAction;
use App\Enums\GeneralStatus;
use App\Models\Subject;

class CreateSubjectAction
{
    public function __construct(
        protected ResolveUniqueSlugAction $resolveUniqueSlug,
    ) {}

    /**
     * Cria uma matéria (SuperAdmin define livremente a instituição de destino).
     *
     * @param  array<string, mixed>  $attributes
     */
    public function __invoke(array $attributes): Subject
    {
        $attributes['is_active'] ??= GeneralStatus::ACTIVE;
        $attributes['slug'] = ($this->resolveUniqueSlug)((string) ($attributes['slug'] ?? $attributes['name']));

        return Subject::create($attributes);
    }
}
