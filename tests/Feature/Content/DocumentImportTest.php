<?php

declare(strict_types=1);

use App\Actions\Content\ImportPdfMaterialAction;
use App\Models\Institution;
use App\Models\StudyMaterial;
use App\Models\Subject;
use App\Models\Test;
use App\Services\Content\DocumentTextExtractor;
use App\Services\Content\PdfSummaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

/**
 * Cria um arquivo .pptx real (zip com slides XML) para os testes.
 *
 * @param  list<string>  $slides
 */
function makePptx(array $slides): string
{
    $path = tempnam(sys_get_temp_dir(), 'pptx') . '.pptx';

    $zip = new ZipArchive();
    $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

    foreach ($slides as $index => $text) {
        $number = $index + 1;
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<p:sld xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main">'
            . '<a:t>' . htmlspecialchars($text, ENT_XML1) . '</a:t>'
            . '</p:sld>';
        $zip->addFromString("ppt/slides/slide{$number}.xml", $xml);
    }

    $zip->close();

    return $path;
}

test('the document extractor reads text from a pptx file', function () {
    $path = makePptx(['Primeiro slide sobre o tema', 'Segundo slide com detalhes']);

    $text = (new DocumentTextExtractor())->extract($path, 'pptx');

    expect($text)->toContain('Primeiro slide')->toContain('Segundo slide');

    @unlink($path);
});

test('the document extractor rejects an unsupported format', function () {
    (new DocumentTextExtractor())->extract('/tmp/whatever.txt', 'txt');
})->throws(RuntimeException::class);

test('the summary splits the content into multiple reading materials', function () {
    $text = "CAPÍTULO 1\n\n"
        . str_repeat('Frase relevante sobre o assunto principal do documento. ', 15)
        . "\n\nCAPÍTULO 2\n\n"
        . str_repeat('Outro ponto importante e detalhado do conteudo estudado. ', 15);

    $materials = (new PdfSummaryService())->buildMaterials($text, 'apostila.pdf');

    expect(count($materials))->toBeGreaterThan(1);
    expect($materials[0])->toHaveKeys(['title', 'content']);
});

test('the summary generates multiple-choice questions from the content', function () {
    $text = str_repeat('O relacionamento eloquent define associacoes entre modelos distintos. ', 4)
        . 'A consulta otimizada evita problemas graves de desempenho no banco de dados. '
        . 'O carregamento adiantado resolve consultas repetidas com bastante eficiencia. '
        . 'A paginacao melhora a performance limitando os resultados retornados ao cliente.';

    $questions = (new PdfSummaryService())->generateQuestions($text, 3);

    expect($questions)->not->toBeEmpty();

    foreach ($questions as $question) {
        expect($question['options'])->toHaveCount(4);
        expect($question['correct_option_index'])
            ->toBeGreaterThanOrEqual(0)
            ->toBeLessThan(4);
        expect($question['options'][$question['correct_option_index']])->toBeString();
    }
});

test('importing a pptx creates study materials and a challenge', function () {
    $institution = Institution::create(['name' => 'School']);
    $subject = Subject::create(['institution_id' => $institution->id, 'name' => 'Matéria']);

    $path = makePptx([
        str_repeat('Introducao ao tema abordando diversos conceitos importantes. ', 20),
        str_repeat('Aprofundamento com exemplos praticos e detalhes tecnicos uteis. ', 20),
        str_repeat('Conclusao revisando os principais pontos estudados no material. ', 20),
    ]);

    $file = new UploadedFile($path, 'deck.pptx', null, null, true);

    $count = app(ImportPdfMaterialAction::class)->execute($subject, $file);

    expect($count)->toBeGreaterThanOrEqual(1);
    expect(StudyMaterial::where('subject_id', $subject->id)->count())->toBe($count);
    expect(Test::where('subject_id', $subject->id)->exists())->toBeTrue();

    @unlink($path);
});

test('importing the same document twice does not duplicate the content', function () {
    $institution = Institution::create(['name' => 'School']);
    $subject = Subject::create(['institution_id' => $institution->id, 'name' => 'Matéria']);

    $slides = [
        str_repeat('Introducao ao tema abordando diversos conceitos importantes. ', 20),
        str_repeat('Aprofundamento com exemplos praticos e detalhes tecnicos uteis. ', 20),
    ];

    $firstFile = new UploadedFile(makePptx($slides), 'deck.pptx', null, null, true);
    app(ImportPdfMaterialAction::class)->execute($subject, $firstFile);

    $materialsAfterFirst = StudyMaterial::where('subject_id', $subject->id)->count();
    expect($materialsAfterFirst)->toBeGreaterThanOrEqual(1);

    // Reenviar o mesmo conteúdo deve ser bloqueado (nada novo a adicionar).
    $secondFile = new UploadedFile(makePptx($slides), 'deck.pptx', null, null, true);
    expect(fn () => app(ImportPdfMaterialAction::class)->execute($subject, $secondFile))
        ->toThrow(RuntimeException::class);

    expect(StudyMaterial::where('subject_id', $subject->id)->count())->toBe($materialsAfterFirst);
});
