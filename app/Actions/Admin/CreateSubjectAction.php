<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Actions\Subject\ResolveUniqueSlugAction;
use App\Models\Subject;

class CreateSubjectAction
{
    public function __construct(
        protected ResolveUniqueSlugAction $resolveUniqueSlug,
    ) {}

    /**
     * Cria uma matéria forçando o vínculo com a instituição do administrador.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function __invoke(array $attributes, int $institutionId): Subject
    {
        $attributes['institution_id'] = $institutionId;
        $attributes['slug'] = ($this->resolveUniqueSlug)((string) ($attributes['slug'] ?? $attributes['name']));

        return Subject::create($attributes);
    }
}
