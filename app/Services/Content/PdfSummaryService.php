<?php

declare(strict_types=1);

namespace App\Services\Content;

/**
 * Transforma o texto de um documento (PDF/PowerPoint) em materiais de estudo,
 * de forma determinística (sem IA): divide o conteúdo em várias partes para
 * leitura, gera um resumo extrativo por parte e monta desafios (quiz).
 */
class PdfSummaryService
{
    /**
     * Stopwords em português ignoradas na pontuação de relevância.
     *
     * @var list<string>
     */
    protected array $stopWords = [
        'a', 'o', 'e', 'de', 'do', 'da', 'dos', 'das', 'em', 'no', 'na', 'nos',
        'nas', 'um', 'uma', 'uns', 'umas', 'que', 'com', 'por', 'para', 'os',
        'as', 'ao', 'aos', 'se', 'ou', 'como', 'mais', 'mas', 'foi', 'ser',
        'sua', 'seu', 'suas', 'seus', 'este', 'esta', 'isso', 'entre', 'sobre',
        'pelo', 'pela', 'são', 'não', 'sem', 'já', 'também', 'quando', 'onde',
        'qual', 'quais', 'ele', 'ela', 'eles', 'elas', 'nós', 'você', 'está',
        'estão', 'ter', 'tem', 'à', 'às', 'the', 'of', 'and', 'to', 'in', 'is',
        'for', 'on', 'with',
    ];

    /**
     * Divide o documento em vários materiais de leitura. Tenta separar por
     * títulos/headings; se não houver, agrupa parágrafos em partes de tamanho
     * equilibrado. Cada material recebe um resumo extrativo + o conteúdo.
     *
     * @return list<array{title: string, content: string}>
     */
    public function buildMaterials(string $text, string $fallbackTitle, int $maxMaterials = 6): array
    {
        $normalized = $this->normalize($text);

        $sections = $this->splitByHeadings($normalized);

        if (count($sections) < 2) {
            $sections = $this->splitByChunks($normalized, $maxMaterials);
        } else {
            $sections = array_slice($sections, 0, $maxMaterials);
        }

        $materials = [];
        $index = 1;

        foreach ($sections as $section) {
            $title = $section['title'] !== ''
                ? $this->cleanTitle($section['title'])
                : $this->fallbackTitleForPart($fallbackTitle, $index);

            $materials[] = [
                'title' => $title,
                'content' => $this->buildSectionMarkdown($title, $section['body']),
            ];

            $index++;
        }

        if ($materials === []) {
            $materials[] = $this->summarize($text, $fallbackTitle);
        }

        return $materials;
    }

    /**
     * Monta o material (título + conteúdo em Markdown) a partir do texto.
     *
     * @return array{title: string, content: string}
     */
    public function summarize(string $text, string $fallbackTitle): array
    {
        $normalized = $this->normalize($text);
        $paragraphs = $this->splitParagraphs($normalized);
        $sentences = $this->splitSentences($normalized);

        $title = $this->guessTitle($paragraphs, $fallbackTitle);
        $summary = $this->extractiveSummary($sentences, 5);
        $topics = $this->keywords($normalized, 8);

        return [
            'title' => $title,
            'content' => $this->buildMarkdown($title, $summary, $topics, $normalized),
        ];
    }

    /**
     * Gera desafios (questões de múltipla escolha) a partir do texto, de forma
     * determinística: seleciona as frases mais relevantes, oculta um termo-chave
     * ("complete a lacuna") e usa outros termos-chave como distratores.
     *
     * @return list<array{question_text: string, options: list<string>, correct_option_index: int}>
     */
    public function generateQuestions(string $text, int $max = 5): array
    {
        $normalized = $this->normalize($text);
        $sentences = $this->splitSentences($normalized);
        $keywords = $this->keywords($normalized, 40);

        if (count($keywords) < 4 || $sentences === []) {
            return [];
        }

        $candidates = $this->extractiveSummary($sentences, $max * 3);

        $questions = [];
        $usedAnswers = [];

        foreach ($candidates as $sentence) {
            if (count($questions) >= $max) {
                break;
            }

            $answer = $this->pickAnswerKeyword($sentence, $keywords, $usedAnswers);

            if ($answer === null) {
                continue;
            }

            $distractors = array_slice(
                array_values(array_filter($keywords, fn ($k) => $k !== $answer)),
                0,
                3,
            );

            if (count($distractors) < 3) {
                continue;
            }

            $usedAnswers[] = $answer;

            $blanked = (string) preg_replace(
                '/' . preg_quote($answer, '/') . '/iu',
                '_____',
                $sentence,
                1,
            );

            $options = array_merge([$answer], $distractors);
            sort($options);
            $correctIndex = (int) array_search($answer, $options, true);

            $questions[] = [
                'question_text' => 'Complete: ' . $blanked,
                'options' => array_map(fn ($o) => mb_convert_case($o, MB_CASE_TITLE), $options),
                'correct_option_index' => $correctIndex,
            ];
        }

        return $questions;
    }

    /**
     * Separa o texto por linhas que parecem títulos/headings.
     *
     * @return list<array{title: string, body: string}>
     */
    protected function splitByHeadings(string $text): array
    {
        $lines = explode("\n", $text);
        $sections = [];
        $current = ['title' => '', 'body' => ''];
        $foundHeading = false;

        foreach ($lines as $line) {
            $trimmed = mb_trim($line);

            if ($this->looksLikeHeading($trimmed)) {
                if ($current['title'] !== '' || mb_trim($current['body']) !== '') {
                    $sections[] = $current;
                }
                $current = ['title' => $trimmed, 'body' => ''];
                $foundHeading = true;

                continue;
            }

            $current['body'] .= $line . "\n";
        }

        if ($current['title'] !== '' || mb_trim($current['body']) !== '') {
            $sections[] = $current;
        }

        if (!$foundHeading) {
            return [];
        }

        return array_values(array_filter(
            $sections,
            fn ($section) => $section['title'] !== '' && mb_trim($section['body']) !== '',
        ));
    }

    /**
     * Agrupa parágrafos em partes de tamanho equilibrado (fallback quando não
     * há títulos detectáveis).
     *
     * @return list<array{title: string, body: string}>
     */
    protected function splitByChunks(string $text, int $max): array
    {
        $paragraphs = $this->splitParagraphs($text);

        if ($paragraphs === []) {
            return [];
        }

        $target = max(1500, (int) ceil(mb_strlen($text) / $max));

        $sections = [];
        $buffer = '';

        foreach ($paragraphs as $paragraph) {
            $buffer .= ($buffer === '' ? '' : "\n\n") . $paragraph;

            if (mb_strlen($buffer) >= $target && count($sections) < $max - 1) {
                $sections[] = ['title' => '', 'body' => $buffer];
                $buffer = '';
            }
        }

        if (mb_trim($buffer) !== '') {
            $sections[] = ['title' => '', 'body' => $buffer];
        }

        return $sections;
    }

    /**
     * Heurística para identificar uma linha de título/heading.
     */
    protected function looksLikeHeading(string $line): bool
    {
        if ($line === '' || mb_strlen($line) > 80) {
            return false;
        }

        // Numerado: "1. Título", "1) Título", "1.2 Título"
        if (preg_match('/^\d+([.)]\d*)*[.)]?\s+\p{L}/u', $line) === 1) {
            return true;
        }

        // Palavras-chave estruturais.
        if (preg_match('/^(cap[íi]tulo|unidade|m[óo]dulo|aula|se[çc][ãa]o|parte|t[óo]pico)\b/iu', $line) === 1) {
            return true;
        }

        // Heading em Markdown.
        if (preg_match('/^#{1,6}\s+\p{L}/u', $line) === 1) {
            return true;
        }

        // Linha curta em CAIXA ALTA (título).
        return mb_strlen($line) >= 4 && mb_strtoupper($line) === $line && preg_match('/\p{Lu}/u', $line) === 1;
    }

    protected function cleanTitle(string $title): string
    {
        $clean = mb_trim((string) preg_replace('/^#{1,6}\s+/', '', $title));

        return $clean !== '' ? $clean : 'Material';
    }

    protected function fallbackTitleForPart(string $fallbackTitle, int $index): string
    {
        $clean = pathinfo($fallbackTitle, PATHINFO_FILENAME);
        $clean = mb_trim((string) preg_replace('/[_-]+/', ' ', $clean));
        $base = $clean !== '' ? mb_convert_case($clean, MB_CASE_TITLE) : 'Material';

        return "{$base} — Parte {$index}";
    }

    protected function buildSectionMarkdown(string $title, string $body): string
    {
        $sentences = $this->splitSentences($body);
        $summary = $this->extractiveSummary($sentences, 3);

        $lines = ["# {$title}", ''];

        if ($summary !== []) {
            $lines[] = '## Resumo';
            $lines[] = '';

            foreach ($summary as $sentence) {
                $lines[] = $sentence;
                $lines[] = '';
            }
        }

        $lines[] = '## Conteúdo';
        $lines[] = '';
        $lines[] = mb_trim($body);

        return implode("\n", $lines);
    }

    /**
     * Escolhe, dentro da frase, um termo-chave ainda não usado como resposta.
     *
     * @param  list<string>  $keywords
     * @param  list<string>  $usedAnswers
     */
    protected function pickAnswerKeyword(string $sentence, array $keywords, array $usedAnswers): ?string
    {
        foreach ($keywords as $keyword) {
            if (in_array($keyword, $usedAnswers, true)) {
                continue;
            }

            if (mb_strlen($keyword) >= 5 && mb_stripos($sentence, $keyword) !== false) {
                return $keyword;
            }
        }

        return null;
    }

    /**
     * Normaliza espaços/quebras mantendo parágrafos.
     */
    protected function normalize(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/[ \t]+/', ' ', $text) ?? $text;
        $text = preg_replace('/\n{3,}/', "\n\n", $text) ?? $text;

        return mb_trim($text);
    }

    /**
     * @return list<string>
     */
    protected function splitParagraphs(string $text): array
    {
        $parts = preg_split('/\n{2,}/', $text) ?: [];

        return array_values(array_filter(array_map('trim', $parts), fn ($p) => mb_strlen($p) > 0));
    }

    /**
     * @return list<string>
     */
    protected function splitSentences(string $text): array
    {
        $flat = preg_replace('/\s+/', ' ', str_replace("\n", ' ', $text)) ?? $text;
        $parts = preg_split('/(?<=[.!?])\s+(?=[A-ZÁÉÍÓÚÂÊÔÃÕÀÇ0-9])/u', mb_trim($flat)) ?: [];

        return array_values(array_filter(
            array_map('trim', $parts),
            fn ($s) => mb_strlen($s) >= 40,
        ));
    }

    /**
     * Escolhe o título: primeira linha curta e significativa ou o fallback
     * (nome do arquivo sem extensão).
     *
     * @param  list<string>  $paragraphs
     */
    protected function guessTitle(array $paragraphs, string $fallbackTitle): string
    {
        $firstLine = mb_trim(explode("\n", $paragraphs[0] ?? '')[0] ?? '');

        if ($firstLine !== '' && mb_strlen($firstLine) <= 120) {
            return $firstLine;
        }

        $clean = pathinfo($fallbackTitle, PATHINFO_FILENAME);
        $clean = mb_trim((string) preg_replace('/[_-]+/', ' ', $clean));

        return $clean !== '' ? mb_convert_case($clean, MB_CASE_TITLE) : 'Material importado';
    }

    /**
     * Resumo extrativo: pontua cada sentença pela frequência de palavras
     * significativas e retorna as melhores mantendo a ordem original.
     *
     * @param  list<string>  $sentences
     * @return list<string>
     */
    protected function extractiveSummary(array $sentences, int $limit): array
    {
        if ($sentences === []) {
            return [];
        }

        $frequencies = $this->wordFrequencies(implode(' ', $sentences));

        $scored = [];

        foreach ($sentences as $index => $sentence) {
            $score = 0;

            foreach ($this->tokenize($sentence) as $word) {
                $score += $frequencies[$word] ?? 0;
            }
            // Normaliza pelo tamanho para não favorecer sentenças longas.
            $words = max(1, count($this->tokenize($sentence)));
            $scored[$index] = $score / $words;
        }

        arsort($scored);
        $topIndexes = array_slice(array_keys($scored), 0, $limit);
        sort($topIndexes);

        return array_map(fn ($i) => $sentences[$i], $topIndexes);
    }

    /**
     * Principais palavras-chave por frequência.
     *
     * @return list<string>
     */
    protected function keywords(string $text, int $limit): array
    {
        $frequencies = $this->wordFrequencies($text);
        arsort($frequencies);

        return array_slice(array_keys($frequencies), 0, $limit);
    }

    /**
     * @return array<string, int>
     */
    protected function wordFrequencies(string $text): array
    {
        $frequencies = [];

        foreach ($this->tokenize($text) as $word) {
            $frequencies[$word] = ($frequencies[$word] ?? 0) + 1;
        }

        return $frequencies;
    }

    /**
     * @return list<string>
     */
    protected function tokenize(string $text): array
    {
        $lower = mb_strtolower($text);
        $words = preg_split('/[^\p{L}\p{N}]+/u', $lower) ?: [];

        return array_values(array_filter(
            $words,
            fn ($word) => mb_strlen($word) >= 4 && !in_array($word, $this->stopWords, true),
        ));
    }

    /**
     * @param  list<string>  $summary
     * @param  list<string>  $topics
     */
    protected function buildMarkdown(string $title, array $summary, array $topics, string $fullText): string
    {
        $lines = ["# {$title}", ''];

        $lines[] = '## Resumo';
        $lines[] = '';

        if ($summary === []) {
            $lines[] = '_Não foi possível gerar um resumo automático deste documento._';
        } else {
            foreach ($summary as $sentence) {
                $lines[] = $sentence;
                $lines[] = '';
            }
        }

        if ($topics !== []) {
            $lines[] = '## Tópicos principais';
            $lines[] = '';

            foreach ($topics as $topic) {
                $lines[] = '- ' . mb_convert_case($topic, MB_CASE_TITLE);
            }
            $lines[] = '';
        }

        $lines[] = '## Conteúdo completo';
        $lines[] = '';
        $lines[] = $fullText;

        return implode("\n", $lines);
    }
}
