<?php

declare(strict_types=1);

namespace Reveal\RevealNeon\Generator;

use Nette\Configurator;
use Nette\DI\ServiceCreationException;
use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use PHPStan\Rules\Registry;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use Reveal\RevealNeon\PHPStan\FileAnalyserProvider;
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

    public function __construct(
        private FileAnalyserProvider $fileAnalyserProvider,
    ) {
    }

    /**
     * @return RuleError[]
     */
    public function analyseConfig(string $servicesFilePath, Registry $registry): array
    {
        $configurator = new Configurator();

        $tempDirectory = __DIR__ . '/local-temp';
        $configurator->setTempDirectory($tempDirectory);
        $configurator->addConfig($servicesFilePath);

        // clear to make sure we work with 1 file at a time
        FileSystem::delete($tempDirectory);

        try {
            $configurator->createContainer();
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
        Assert::isArray($containerCachedFiles);
        Assert::count($containerCachedFiles, 1);
        Assert::keyExists($containerCachedFiles, 0);

        $containerCacheFile = $containerCachedFiles[0];

        $fileAnalyser = $this->fileAnalyserProvider->provide();

        $result = $fileAnalyser->analyseFile($containerCacheFile, [], $registry, null);

        $ruleErrors = [];
        foreach ($result->getErrors() as $error) {
            $ruleErrors[] = RuleErrorBuilder::message($error->getMessage())
                ->file($error->getFile())
                ->line($error->getLine())
                ->build();
        }

        // 2. clean it from decoration code

        // 3. analyse the clean file

        return $ruleErrors;
    }
}
