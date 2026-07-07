<?php

declare(strict_types=1);

namespace App\Services\Content;

use RuntimeException;
use Smalot\PdfParser\Parser;
use Throwable;
use ZipArchive;

/**
 * Extrai o texto bruto de documentos de conteúdo (PDF e PowerPoint .pptx).
 * O PPTX é um zip de XML — lemos os slides sem depender de libs externas.
 */
class DocumentTextExtractor
{
    /**
     * @throws RuntimeException quando o formato é inválido, ilegível ou vazio.
     */
    public function extract(string $absolutePath, string $extension): string
    {
        $text = match (mb_strtolower($extension)) {
            'pdf' => $this->fromPdf($absolutePath),
            'pptx' => $this->fromPptx($absolutePath),
            default => throw new RuntimeException('Formato não suportado. Envie um PDF ou PowerPoint (.pptx).'),
        };

        $text = trim($text);

        if ($text === '') {
            throw new RuntimeException('O arquivo não contém texto extraível (pode ser digitalizado/somente imagens).');
        }

        return $text;
    }

    protected function fromPdf(string $path): string
    {
        try {
            return (new Parser())->parseFile($path)->getText();
        } catch (Throwable $exception) {
            throw new RuntimeException('Não foi possível ler o PDF: ' . $exception->getMessage(), $exception->getCode(), previous: $exception);
        }
    }

    protected function fromPptx(string $path): string
    {
        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new RuntimeException('Não foi possível abrir o arquivo PowerPoint (.pptx).');
        }

        // Coleta os XMLs de slides e os ordena pelo número do slide.
        $slides = [];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = (string) $zip->getNameIndex($i);

            if (preg_match('#^ppt/slides/slide(\d+)\.xml$#', $name, $matches) === 1) {
                $slides[(int) $matches[1]] = $name;
            }
        }

        ksort($slides);

        $parts = [];

        foreach ($slides as $name) {
            $xml = (string) $zip->getFromName($name);

            if (preg_match_all('/<a:t>(.*?)<\/a:t>/su', $xml, $found) === false) {
                continue;
            }

            $slideText = trim(implode(' ', array_map(
                static fn (string $fragment): string => html_entity_decode(strip_tags($fragment), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                $found[1],
            )));

            if ($slideText !== '') {
                $parts[] = $slideText;
            }
        }

        $zip->close();

        return implode("\n\n", $parts);
    }
}
