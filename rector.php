<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;
use RectorLaravel\Set\LaravelLevelSetList;
use RectorLaravel\Set\LaravelSetList;
use Rector\ValueObject\PhpVersion;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/app',
        __DIR__ . '/routes',
        __DIR__ . '/database',
        __DIR__ . '/resources',
        __DIR__ . '/tests',
        __DIR__ . '/app/Http/Controllers',
    ]);

    $rectorConfig->phpVersion(PhpVersion::PHP_84);

    $rectorConfig->sets([
        SetList::CODE_QUALITY,
        LaravelSetList::LARAVEL_CODE_QUALITY,
        LaravelLevelSetList::UP_TO_LARAVEL_110,
        LaravelSetList::LARAVEL_TYPE_DECLARATIONS,
        LaravelSetList::LARAVEL_COLLECTION,
        LaravelSetList::LARAVEL_IF_HELPERS,
        LaravelSetList::LARAVEL_ARRAYACCESS_TO_METHOD_CALL// adapte selon ta version de Laravel
    ]);

    $rectorConfig->skip([
        __DIR__ . '/vendor',
        __DIR__ . '/storage',
        __DIR__ . '/bootstrap/cache',
    ]);
};
