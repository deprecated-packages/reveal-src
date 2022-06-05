<?php

declare(strict_types=1);

use PHPStan\Analyser\FileAnalyser;
use PHPStan\Parser\Parser;
use Reveal\TemplatePHPStanCompiler\PHPStan\PHPStanServicesFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/../../../vendor/symplify/astral/config/config.php');

    $services = $containerConfigurator->services();
    $services->defaults()
        ->public()
        ->autowire();

    $services->load('Reveal\\TemplatePHPStanCompiler\\', __DIR__ . '/../src')
        // created manually
        ->exclude([
            __DIR__ . '/../src/NodeVisitor',
            __DIR__ . '/../src/Rules/TemplateRulesRegistry.php',
            __DIR__ . '/../src/ValueObject',
        ]);

    // phpstan services
    $services->set(FileAnalyser::class)
        ->factory([service(PHPStanServicesFactory::class), 'createFileAnalyser']);

    $services->set(Parser::class)
        ->factory([service(PHPStanServicesFactory::class), 'createPHPStanParser']);
};
