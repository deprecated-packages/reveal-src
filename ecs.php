<?php

declare(strict_types=1);

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
        \PhpCsFixer\Fixer\PhpUnit\PhpUnitStrictFixer::class => [
            // compare content of 2 objects, not identical object
            __DIR__ . '/packages/reveal-neon/tests/Generator/DependencyContainerAnalyzerTest.php',
        ],
    ]);

    // run and fix, one by one
    $containerConfigurator->import(SetList::COMMON);
    $containerConfigurator->import(SetList::PSR_12);
};
