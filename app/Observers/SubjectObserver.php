<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Subject;
use Illuminate\Support\Str;

class SubjectObserver
{
    /**
     * Garante um slug único (por instituição) antes de criar a matéria.
     */
    public function creating(Subject $subject): void
    {
        $subject->slug = $this->uniqueSlug($subject);
    }

    /**
     * Reaplica a unicidade do slug quando ele (ou o nome de origem) muda.
     */
    public function updating(Subject $subject): void
    {
        if ($subject->isDirty('slug') || ($subject->slug === null || $subject->slug === '')) {
            $subject->slug = $this->uniqueSlug($subject);
        }
    }

    /**
     * Resolve um slug único dentro da instituição, derivando do nome quando vazio
     * e adicionando sufixo incremental em caso de colisão.
     */
    private function uniqueSlug(Subject $subject): string
    {
        $base = $subject->slug !== null && $subject->slug !== ''
            ? Str::slug($subject->slug)
            : Str::slug((string) $subject->name);

        if ($base === '') {
            $base = 'subject-' . Str::random(6);
        }

        $slug = $base;
        $suffix = 1;

        while ($this->slugExists($subject, $slug)) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }

    private function slugExists(Subject $subject, string $slug): bool
    {
        return Subject::query()
            ->where('institution_id', $subject->institution_id)
            ->where('slug', $slug)
            ->when($subject->exists, fn ($query) => $query->whereKeyNot($subject->getKey()))
            ->exists();
    }
}
