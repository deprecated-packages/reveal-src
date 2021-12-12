<?php

declare(strict_types=1);

namespace Reveal\RevealNeon\Generator;

use Nette\Utils\FileSystem;

/**
 * @see \Reveal\RevealNeon\Tests\Generator\GenerateTest
 */
final class Generator
{
    public function fromConfig(string $servicesFilePath): string
    {
        $fileContent = FileSystem::read($servicesFilePath);
        dump($fileContent);
        die;
    }
}
