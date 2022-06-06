<?php

declare(strict_types=1);

namespace Reveal\TemplatePHPStanCompiler\PHPStan;

use PHPStan\Analyser\FileAnalyser;
use PHPStan\DependencyInjection\ContainerFactory;
use function getcwd;

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

    public function provide(): FileAnalyser
    {
        if ($this->fileAnalyser instanceof FileAnalyser) {
            return $this->fileAnalyser;
        }

        /** Inspiration @see https://github.com/rectorphp/rector-src/blob/main/packages/NodeTypeResolver/DependencyInjection/PHPStanServicesFactory.php $containerFactory */
        $containerFactory = new ContainerFactory(getcwd());
        $additionalConfigs = [
            __DIR__ . '/../../config/php-parser.neon',
        ];

        $container = $containerFactory->create(\sys_get_temp_dir(), $additionalConfigs, []);
        $fileAnalyser = $container->getByType(FileAnalyser::class);

        $this->fileAnalyser = $fileAnalyser;

        return $fileAnalyser;
    }
}
