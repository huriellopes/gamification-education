<?php

declare(strict_types=1);

namespace App\Support;

class SpreadsheetSanitizer
{
    /**
     * Caracteres que Excel/LibreOffice interpretam como início de fórmula ao
     * abrir um CSV/XLSX. Qualquer valor exportado que comece com um deles
     * precisa ser neutralizado.
     */
    private const FORMULA_PREFIXES = ['=', '+', '-', '@', "\t", "\r"];

    /**
     * Neutraliza CSV/Excel Formula Injection (CWE-1436) nas linhas antes de
     * gravar a planilha: prefixa com apóstrofo qualquer célula cujo valor
     * comece com um caractere que o Excel/LibreOffice interpretaria como
     * fórmula. Sem isso, texto vindo do usuário (nome de cadastro, User-Agent
     * de uma visita anônima ao site público, etc.) pode virar uma fórmula
     * executada quando um admin abre o arquivo exportado.
     *
     * @param  array<int, array<int, mixed>>  $rows
     * @return array<int, array<int, mixed>>
     */
    public static function sanitizeRows(array $rows): array
    {
        return array_map(
            static fn (array $row): array => array_map(self::sanitizeCell(...), $row),
            $rows,
        );
    }

    public static function sanitizeCell(mixed $value): mixed
    {
        if (!is_string($value) || $value === '') {
            return $value;
        }

        return in_array($value[0], self::FORMULA_PREFIXES, true)
            ? "'" . $value
            : $value;
    }
}
