<?php

declare(strict_types=1);

namespace App\Services\System;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use SplFileObject;

/**
 * Acesso somente-leitura aos arquivos de log da aplicação (listagem e leitura
 * das últimas linhas). Mantém o I/O de arquivos e a formatação fora do
 * controller.
 */
class LogFileService
{
    /**
     * Número de linhas finais carregadas ao abrir um arquivo de log.
     */
    private const TAIL_LINES = 300;

    /**
     * Lista os arquivos .log disponíveis, do mais recente ao mais antigo.
     *
     * @return list<array{name: string, size: string, modified_at: string, raw_size: int, raw_modified: int}>
     */
    public function list(): array
    {
        $logPath = storage_path('logs');
        $files = File::exists($logPath) ? File::files($logPath) : [];

        return collect($files)
            ->filter(fn ($file) => $file->getExtension() === 'log')
            ->map(fn ($file) => [
                'name' => $file->getFilename(),
                'size' => $this->formatBytes($file->getSize()),
                'modified_at' => Carbon::createFromTimestamp($file->getMTime())->format('d/m/Y H:i:s'),
                'raw_size' => $file->getSize(),
                'raw_modified' => $file->getMTime(),
            ])
            ->sortByDesc('raw_modified')
            ->values()
            ->all();
    }

    /**
     * Lê as últimas linhas de um arquivo de log. Retorna null quando nenhum
     * arquivo é informado ou ele não existe.
     *
     * @return array{name: string, content: string, total_lines: int, loaded_lines: int}|null
     */
    public function read(?string $fileName): ?array
    {
        if ($fileName === null || $fileName === '') {
            return null;
        }

        $name = basename($fileName);
        $path = storage_path('logs/' . $name);

        if (!File::exists($path)) {
            return null;
        }

        $file = new SplFileObject($path, 'r');
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key();

        $file->seek(max(0, $totalLines - self::TAIL_LINES));

        $lines = [];

        while (!$file->eof()) {
            $line = $file->fgets();

            if ($line !== false) {
                $lines[] = mb_trim($line);
            }
        }

        return [
            'name' => $name,
            'content' => implode("\n", array_filter($lines)),
            'total_lines' => $totalLines,
            'loaded_lines' => count($lines),
        ];
    }

    /**
     * Formata bytes em uma unidade legível.
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = (int) floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
