<?php

declare(strict_types=1);

namespace Reveal\TemplatePHPStanCompiler\PHPStan;

use PHPStan\Analyser\FileAnalyser;
use PHPStan\DependencyInjection\DerivativeContainerFactory;

/**
 * @api
 *
 * This file analyser creates custom PHPStan DI container, based on rich php-parser with parent connection etc.
 *
 * It allows full analysis of just-in-time PHP files since PHPStan 1.0
 */
final class FileAnalyserProvider
{
    private FileAnalyser|null $fileAnalyser = null;

    public function __construct(
        private DerivativeContainerFactory $derivativeContainerFactory
    ) {
    }

    public function provide(): FileAnalyser
    {
        if ($this->fileAnalyser instanceof FileAnalyser) {
            return $this->fileAnalyser;
        }

        $container = $this->derivativeContainerFactory->create([__DIR__ . '/../../config/php-parser.neon']);
        $fileAnalyser = $container->getByType(FileAnalyser::class);

        $this->fileAnalyser = $fileAnalyser;

        return $fileAnalyser;
    }
}
