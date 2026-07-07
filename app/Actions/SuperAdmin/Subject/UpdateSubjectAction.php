<?php

declare(strict_types=1);

namespace App\Actions\SuperAdmin\Subject;

use App\Actions\Subject\ResolveUniqueSlugAction;
use App\Models\Subject;

class UpdateSubjectAction
{
    public function __construct(
        protected ResolveUniqueSlugAction $resolveUniqueSlug,
    ) {}

    /**
     * Atualiza uma matéria. Caso o SuperAdmin mova a matéria para outra
     * instituição, o vínculo com a turma é desfeito (a turma pertence à
     * instituição antiga), evitando um classroom_id pendurado e inconsistente.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function __invoke(Subject $subject, array $attributes): Subject
    {
        if (isset($attributes['slug'])) {
            $attributes['slug'] = ($this->resolveUniqueSlug)((string) $attributes['slug'], $subject->id);
        }

        if (
            array_key_exists('institution_id', $attributes)
            && (int) $attributes['institution_id'] !== (int) $subject->institution_id
        ) {
            $attributes['classroom_id'] = null;
        }

        $subject->update($attributes);

        return $subject;
    }
}
