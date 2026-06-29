<?php

declare(strict_types=1);

namespace App\Actions\Teacher;

use App\Models\Question;

class DeleteQuestionAction
{
    /**
     * Exclui uma questão.
     */
    public function __invoke(Question $question): void
    {
        $question->delete();
    }
}
