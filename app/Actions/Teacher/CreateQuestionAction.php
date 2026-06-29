<?php

declare(strict_types=1);

namespace App\Actions\Teacher;

use App\Models\Question;
use App\Models\Test;

class CreateQuestionAction
{
    /**
     * Cria uma questão vinculada ao teste.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function __invoke(array $attributes, Test $test): Question
    {
        $attributes['test_id'] = $test->id;

        return Question::create($attributes);
    }
}
