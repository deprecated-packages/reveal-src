<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\PhpUnit\PhpUnitStrictFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PATHS, [
        __DIR__ . '/ecs.php',
        __DIR__ . '/packages',
    ]);

    $parameters->set(Option::SKIP, [
        '*/Source/*',
        PhpUnitStrictFixer::class => [
            // compare content of 2 objects, not identical object
            __DIR__ . '/packages/reveal-neon/tests/Generator/DependencyContainerAnalyzerTest.php',
        ],

        __DIR__ . '/packages/twig-phpstan-compiler/tests/TwigToPhpCompiler/FixtureWithTypes/',
    ]);

    // run and fix, one by one
    $containerConfigurator->import(SetList::CLEAN_CODE);
    $containerConfigurator->import(SetList::COMMON);
    $containerConfigurator->import(SetList::PSR_12);
};
