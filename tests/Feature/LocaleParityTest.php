<?php

declare(strict_types=1);

use Illuminate\Support\Arr;

/*
|--------------------------------------------------------------------------
| Paridade de i18n
|--------------------------------------------------------------------------
|
| Garante que pt_BR e en tenham exatamente o mesmo conjunto de chaves de
| tradução. Impede o gotcha silencioso de adicionar uma chave em um locale e
| esquecer no outro (o helper __() cairia no fallback exibindo a chave crua).
|
*/

/**
 * @return list<string>
 */
function localeGroups(): array
{
    return array_map(
        fn (string $path): string => basename($path, '.php'),
        glob(dirname(__DIR__, 2) . '/lang/pt_BR/*.php') ?: [],
    );
}

test('cada grupo de tradução existe em ambos os locales', function () {
    $root = dirname(__DIR__, 2);
    $pt = array_map(fn ($p) => basename($p), glob("{$root}/lang/pt_BR/*.php") ?: []);
    $en = array_map(fn ($p) => basename($p), glob("{$root}/lang/en/*.php") ?: []);

    sort($pt);
    sort($en);

    expect($en)->toBe($pt);
});

test('pt_BR e en têm exatamente as mesmas chaves', function (string $group) {
    $root = dirname(__DIR__, 2);
    $pt = array_keys(Arr::dot(require "{$root}/lang/pt_BR/{$group}.php"));
    $en = array_keys(Arr::dot(require "{$root}/lang/en/{$group}.php"));

    sort($pt);
    sort($en);

    expect($en)->toBe($pt, "As chaves do grupo '{$group}' divergem entre pt_BR e en.");
})->with(localeGroups());
