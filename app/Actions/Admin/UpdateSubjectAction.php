<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Actions\Subject\ResolveUniqueSlugAction;
use App\Models\Subject;

class UpdateSubjectAction
{
    public function __construct(
        protected ResolveUniqueSlugAction $resolveUniqueSlug,
    ) {}

    /**
     * Atualiza uma matéria mantendo o vínculo de instituição imutável.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function __invoke(Subject $subject, array $attributes): Subject
    {
        unset($attributes['institution_id']);

        if (isset($attributes['slug'])) {
            $attributes['slug'] = ($this->resolveUniqueSlug)((string) $attributes['slug'], $subject->id);
        }

        $subject->update($attributes);

        return $subject;
    }
}
