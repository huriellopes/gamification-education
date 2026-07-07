<?php

declare(strict_types=1);

namespace App\Actions\Teacher;

use App\Actions\Subject\ResolveUniqueSlugAction;
use App\Enums\GeneralStatus;
use App\Models\Subject;
use App\Models\User;

class CreateSubjectAction
{
    public function __construct(
        protected ResolveUniqueSlugAction $resolveUniqueSlug,
    ) {}

    /**
     * Cria uma matéria e a associa ao professor informado.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function __invoke(array $attributes, User $teacher): Subject
    {
        $attributes['institution_id'] = $teacher->institution_id;
        $attributes['is_active'] = GeneralStatus::ACTIVE;
        $attributes['slug'] = ($this->resolveUniqueSlug)((string) ($attributes['slug'] ?? $attributes['name']));

        $subject = Subject::create($attributes);
        $subject->teachers()->attach($teacher->id);

        return $subject;
    }
}
