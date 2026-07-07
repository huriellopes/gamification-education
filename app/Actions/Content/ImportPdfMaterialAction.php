<?php

declare(strict_types=1);

namespace App\Actions\Content;

use App\Models\Question;
use App\Models\StudyMaterial;
use App\Models\Subject;
use App\Models\Test;
use App\Services\Content\DocumentTextExtractor;
use App\Services\Content\PdfSummaryService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ImportPdfMaterialAction
{
    public function __construct(
        protected DocumentTextExtractor $extractor,
        protected PdfSummaryService $summary,
    ) {}

    /**
     * Lê o documento enviado (PDF ou PowerPoint), divide-o em vários materiais
     * de leitura e gera um desafio (quiz) com questões derivadas do conteúdo.
     *
     * @return int Quantidade de materiais criados.
     *
     * @throws RuntimeException quando o arquivo não pode ser lido ou está vazio.
     */
    public function execute(Subject $subject, UploadedFile $file): int
    {
        $extension = mb_strtolower($file->getClientOriginalExtension());
        $text = $this->extractor->extract((string) $file->getRealPath(), $extension);

        $materials = $this->summary->buildMaterials($text, $file->getClientOriginalName());
        $questions = $this->summary->generateQuestions($text, 5);

        // Deduplicação: descarta materiais cujo conteúdo já existe na matéria
        // (evita duplicar ao reenviar o mesmo arquivo/conteúdo) e também
        // repetições dentro do próprio upload.
        $seenHashes = StudyMaterial::query()
            ->where('subject_id', $subject->id)
            ->whereNotNull('source_hash')
            ->pluck('source_hash')
            ->all();

        $newMaterials = [];

        foreach ($materials as $material) {
            $hash = hash('sha256', $material['content']);

            if (in_array($hash, $seenHashes, true)) {
                continue;
            }

            $seenHashes[] = $hash;
            $newMaterials[] = ['title' => $material['title'], 'content' => $material['content'], 'source_hash' => $hash];
        }

        if ($newMaterials === []) {
            throw new RuntimeException('Este conteúdo já foi importado para esta matéria — nenhum material novo a adicionar.');
        }

        return DB::transaction(function () use ($subject, $newMaterials, $questions): int {
            foreach ($newMaterials as $material) {
                StudyMaterial::create([
                    'subject_id' => $subject->id,
                    'title' => $material['title'],
                    'content' => $material['content'],
                    'points_reward' => 15,
                    'source_hash' => $material['source_hash'],
                ]);
            }

            if ($questions !== []) {
                $test = Test::create([
                    'subject_id' => $subject->id,
                    'title' => 'Desafio: ' . $newMaterials[0]['title'],
                    'description' => 'Atividade gerada automaticamente a partir do documento importado.',
                    'points_reward' => 50,
                ]);

                foreach ($questions as $question) {
                    Question::create([
                        'test_id' => $test->id,
                        'question_text' => $question['question_text'],
                        'options' => $question['options'],
                        'correct_option_index' => $question['correct_option_index'],
                    ]);
                }
            }

            return count($newMaterials);
        });
    }
}
