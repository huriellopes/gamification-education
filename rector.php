<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;
use RectorLaravel\Rector\StaticCall\CarbonToDateFacadeRector;
use RectorLaravel\Set\LaravelSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/database',
        __DIR__ . '/routes',
    ])
    ->withSets([
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        LaravelSetList::LARAVEL_CODE_QUALITY,
    ])
    ->withSkip([
        // Opinativa: mantemos o uso direto de Carbon em vez do facade Date.
        CarbonToDateFacadeRector::class,
    ])
    // Cache dentro do projeto (evita problemas de permissão no /tmp do contêiner Sail).
    ->withCache(
        cacheDirectory: __DIR__ . '/storage/framework/cache/rector',
        containerCacheDirectory: __DIR__ . '/storage/framework/cache',
    );
