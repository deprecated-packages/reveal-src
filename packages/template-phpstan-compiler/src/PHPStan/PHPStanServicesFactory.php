<?php

declare(strict_types=1);

namespace Reveal\TemplatePHPStanCompiler\PHPStan;

use PHPStan\Analyser\FileAnalyser;
use PHPStan\DependencyInjection\Container;
use PHPStan\DependencyInjection\ContainerFactory;
use PHPStan\Parser\Parser;

/**
 * @see https://github.com/rectorphp/rector-src/blob/main/packages/NodeTypeResolver/DependencyInjection/PHPStanServicesFactory.php
 */
final class PHPStanServicesFactory
{
    private readonly Container $container;

    public function __construct()
    {
        $containerFactory = new ContainerFactory(getcwd());

        $additionalConfigs = [
            __DIR__ . '/../../config/php-parser.neon',
        ];
        $this->container = $containerFactory->create(sys_get_temp_dir(), $additionalConfigs, []);
    }

    public function createFileAnalyser(): FileAnalyser
    {
        return $this->container->getByType(FileAnalyser::class);
    }

    public function createPHPStanParser(): Parser
    {
        return $this->container->getService('currentPhpVersionRichParser');
    }
}
