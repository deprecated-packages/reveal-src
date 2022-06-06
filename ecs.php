<?php

declare(strict_types=1);

use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths([
        __DIR__ . '/ecs.php',
        __DIR__ . '/scoper.php',
        __DIR__ . '/packages',
    ]);

    $ecsConfig->skip([
        '*/Source/*',
        __DIR__ . '/packages/TWIGPHPStanCompiler/tests/TwigToPhpCompiler/FixtureWithTypes/',
        __DIR__ . '/packages/LattePHPStanCompiler/tests/LatteToPhpCompiler/Fixture*',
    ]);

    // run and fix, one by one
    $ecsConfig->sets([
        SetList::CLEAN_CODE,
        SetList::COMMON,
        SetList::PSR_12,
    ]);
};
