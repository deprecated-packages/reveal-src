<?php

declare(strict_types=1);

namespace Reveal\RevealNeon\Generator;

use Nette\Configurator;
use Nette\DI\ServiceCreationException;
use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use Webmozart\Assert\Assert;

/**
 * @see \Reveal\RevealNeon\Tests\Generator\DependencyContainerAnalyzerTest
 */
final class DependencyContainerAnalyzer
{
    /**
     * @see https://regex101.com/r/zzqJOQ/1
     * @var string
     */
    private const CLASS_NAME_REGEX = "#Class '(?<class_name>.*?)' not found#";

    /**
     * @return RuleError[]
     */
    public function analyseConfig(string $servicesFilePath): array
    {
        $configurator = new Configurator();

        $tempDirectory = __DIR__ . '/local-temp';
        $configurator->setTempDirectory($tempDirectory);
        $configurator->addConfig($servicesFilePath);

        // clear to make sure we work with 1 file at a time
        FileSystem::delete($tempDirectory);

        try {
            $container = $configurator->createContainer();
        } catch (ServiceCreationException $serviceCreationException) {
            $match = Strings::match($serviceCreationException->getMessage(), self::CLASS_NAME_REGEX);
            if ($match !== null) {
                $className = $match['class_name'];

                $errorMessage = sprintf('Class "%s" was not found', $className);
                $ruleError = RuleErrorBuilder::message($errorMessage)
                    // @todo add line!
                    ->file($servicesFilePath)
                    ->build();

                return [$ruleError];
            }

            throw $serviceCreationException;
        }

        // 1. get build container file path
        $cacheDirectory = $tempDirectory . '/cache/nette.configurator';

        $containerCachedFiles = glob($cacheDirectory . '/*.php');
        Assert::count($containerCachedFiles, 1);

        $containerCacheFile = $containerCachedFiles[0];
        dump($containerCacheFile);
        die;

        // 2. clean it from decoration code

        // 3. analyse the clean file

        // @todo analyse errors
        dump('___');
        die;

        return [];
    }
}
