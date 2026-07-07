<?php

declare(strict_types=1);

namespace App\Actions\Subject;

use App\Models\Subject;
use Illuminate\Support\Str;

class ResolveUniqueSlugAction
{
    /**
     * Garante um slug único na tabela de matérias. Caso o slug desejado já
     * exista, acrescenta um sufixo incremental (ex.: "teste", "teste-1",
     * "teste-2"...). Em atualizações, o próprio registro é ignorado para que
     * salvar sem alterar o slug não gere um sufixo desnecessário.
     */
    public function __invoke(string $desired, ?int $ignoreId = null): string
    {
        $base = Str::slug($desired) ?: 'materia';
        $slug = $base;
        $suffix = 1;

        while (
            Subject::query()
                ->where('slug', $slug)
                ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
