<?php

declare(strict_types=1);

namespace App\Http\Resources\Student;

use App\Models\Question;
use App\Models\Test;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Test
 */
class TestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * Carrega as questões sem enviar o índice correto para o cliente (segurança).
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Collection<int, Question> $questions */
        $questions = $this->questions()->get();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'points_reward' => $this->points_reward,
            'questions' => $questions->map(fn (Question $question) => [
                'id' => $question->id,
                'question_text' => $question->question_text,
                'options' => $question->options,
            ])->all(),
        ];
    }
}
