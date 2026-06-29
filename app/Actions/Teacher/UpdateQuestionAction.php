<?php

declare(strict_types=1);

namespace App\Actions\Teacher;

use App\Models\Question;

class UpdateQuestionAction
{
    /**
     * Atualiza uma questão.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function __invoke(Question $question, array $attributes): Question
    {
        unset($attributes['test_id']);

        $question->update($attributes);

        return $question;
    }
}
