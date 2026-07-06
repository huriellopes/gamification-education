<?php

declare(strict_types=1);

namespace App\Actions\Content;

use App\Models\Question;
use App\Models\Subject;
use App\Models\Test;
use App\Services\Content\MaterialGenerationService;
use Illuminate\Support\Facades\DB;

class GenerateTestForSubjectAction
{
    protected MaterialGenerationService $generationService;

    public function __construct(MaterialGenerationService $generationService)
    {
        $this->generationService = $generationService;
    }

    /**
     * Gera um teste e suas questões a partir de um tema.
     */
    public function execute(Subject $subject, string $theme): Test
    {
        return DB::transaction(function () use ($subject, $theme) {
            // Gera as questões estruturadas baseadas no tema
            $generatedData = $this->generationService->generateTestData($theme);

            // Cria o teste principal
            $test = Test::create([
                'subject_id' => $subject->id,
                'title' => $generatedData['title'],
                'description' => $generatedData['description'],
                'points_reward' => 50, // Padrão de 50 pontos por atividade
            ]);

            // Cria cada questão associada
            foreach ($generatedData['questions'] as $qData) {
                Question::create([
                    'test_id' => $test->id,
                    'question_text' => $qData['question_text'],
                    'options' => $qData['options'],
                    'correct_option_index' => $qData['correct_option_index'],
                ]);
            }

            return $test->load('questions');
        });
    }
}
