<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\Astral\PhpParser\SmartPhpParser;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/../../template-phpstan-compiler/config/services.php');

    $services = $containerConfigurator->services();
    $services->defaults()
        ->public()
        ->autowire();

    $services->load('Reveal\\TwigPHPStanCompiler\\', __DIR__ . '/../src')
        ->exclude([
            __DIR__ . '/../src/PhpParser/NodeVisitor/TwigGetAttributeExpanderNodeVisitor.php',
            __DIR__ . '/../src/Twig/TolerantTwigEnvironment.php',
            __DIR__ . '/../src/ValueObject',
        ]);

    $services->set(SmartPhpParser::class);
    $services->set(\PhpParser\PrettyPrinter\Standard::class);
};
